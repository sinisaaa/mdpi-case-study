<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Command\CreateBook\CreateBookCommand;
use App\DTO\CreateBookDto;
use App\Entity\Book;
use App\Service\Messenger\MessageBusWrapper;
use App\Service\Pagination\Enum\PaginationTypeEnum;
use App\Service\Pagination\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View as ApiView;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

final class BookController extends AbstractController
{

    /**
     * @param EntityManagerInterface $em
     * @param PaginationService $paginationService
     * @param MessageBusWrapper $bus
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaginationService $paginationService,
        private readonly MessageBusWrapper $bus
    )
    {
    }

    #[OA\Get(
        path: '/books',
        summary: 'Get a paginated list of books',
        tags: ['Book'],
        parameters: [
            new OA\QueryParameter(name: 'pagination[pagination_type]', description: 'Pagination type (see PaginationTypeEnum)', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'pagination[page]', description: 'Page number for offset pagination', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'pagination[limit]', description: 'Number of items per page', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'pagination[cursor]', description: 'Cursor for cursor-based pagination', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'pagination[field]', description: 'Field for cursor-based pagination', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'pagination[direction]', description: 'Direction for cursor-based pagination', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with paginated books',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: new Model(type: Book::class, groups: ['search']))),
                        new OA\Property(
                            property: 'meta',
                            type: 'object'
                        ),
                    ],
                    type: 'object'
                )
            )
        ]
    )]
    #[Route('/books', name: 'books_index', methods: ['GET'])]
    #[View(statusCode: Response::HTTP_OK, serializerGroups: ['book_list'])]
    public function bookListAction(Request $request): ApiView
    {
        $paginationParams = array_intersect_key($request->query->all('pagination'), ['pagination_type' => '', 'page' => '', 'limit' => '', 'cursor' => '', 'field' => '', 'direction' => '']);
        $paginationType = PaginationTypeEnum::tryFrom($paginationParams['pagination_type'] ?? '') ?? PaginationTypeEnum::OFFSET;

        $booksQuery = $this->em->getRepository(Book::class)->findAllQuery();
        $searchResult = $this->paginationService->paginate($booksQuery, $paginationParams, $paginationType);

        return ApiView::create($searchResult->jsonSerialize(), Response::HTTP_OK);
    }

    #[OA\Get(
        path: '/books/{id}',
        summary: 'Get a book by its ID',
        tags: ['Book'],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'ID of the book',
                required: true,
                schema: new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response with the book',
                content: new OA\JsonContent(
                    ref: new Model(type: Book::class, groups: ['book_detail'])
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Book not found'
            )
        ]
    )]
    #[Route('/books/{id}', name: 'book_detail', methods: ['GET'])]
    #[View(statusCode: Response::HTTP_OK, serializerGroups: ['book_detail', 'author_ref'])]
    public function bookDetailsAction(Book $book): ApiView
    {
        return ApiView::create($book, Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/books',
        summary: 'Create a new book',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                ref: new Model(type: CreateBookDto::class)
            )
        ),
        tags: ['Book'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Book created successfully',
                content: new OA\JsonContent(
                    ref: new Model(type: Book::class)
                )
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input'
            )
        ]
    )]
    #[Route('/books', name: 'api_books_create', methods: ['POST'])]
    #[View(statusCode: Response::HTTP_CREATED, serializerGroups: ['book_detail', 'author_ref'])]
    public function createBookAction(#[MapRequestPayload] CreateBookDto $dto): ApiView
    {
        if ($dto->isbn && null !==$this->em->getRepository(Book::class)->findByIsbn($dto->isbn)) {
            throw new ConflictHttpException('A book with this ISBN already exists.');
        }

        $book = $this->bus->dispatchWithResult(
            new CreateBookCommand(
                $dto->title,
                $dto->authors,
                $dto->published,
                $dto->isbn)
        );

        return ApiView::create($book, Response::HTTP_CREATED);
    }

}
