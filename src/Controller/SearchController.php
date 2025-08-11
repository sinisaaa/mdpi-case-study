<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Enum\BookSortTypeEnum;
use App\Service\Pagination\Enum\PaginationTypeEnum;
use App\Service\Pagination\PaginationService;
use App\Service\Validation\BookSearchRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations\View;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\View\View as ApiView;
use OpenApi\Attributes as OA;

final class SearchController extends AbstractController
{

    /**
     * @param EntityManagerInterface $em
     * @param PaginationService $paginationService
     * @param BookSearchRequestValidator $bookSearchRequestValidator
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PaginationService $paginationService,
        private readonly BookSearchRequestValidator $bookSearchRequestValidator
    )
    {
    }


    #[OA\Get(
        path: '/search',
        description: 'Returns a paginated list of books matching the search query.',
        summary: 'Search for books',
        tags: ['Search'],
        parameters: [
            new OA\QueryParameter(name: 'q', description: 'Search query string', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'sort', description: 'Sort type (see BookSortTypeEnum)', required: false, schema: new OA\Schema(type: 'string')),
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
                description: 'Successful search result',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'items',
                            type: 'array',
                            items: new OA\Items(ref: new Model(type: Book::class, groups: ['book_list']))
                        ),
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
    #[Route('/search', name: 'search_index', methods: ['GET'])]
    #[View(statusCode: Response::HTTP_OK, serializerGroups: ['search', 'author_ref'])]
    public function bookSearchAction(Request $request): ApiView
    {
        $errors = $this->bookSearchRequestValidator->validate($request);

        if (0 < count($errors)) {
            return ApiView::create(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }

        $searchQuery = $request->query->get('q');
        $sortType = BookSortTypeEnum::tryFrom($request->query->get('sort') ?? '');
        $paginationParams = array_intersect_key($request->query->all('pagination'), ['pagination_type' => '', 'page' => '', 'limit' => '', 'cursor' => '', 'field' => '', 'direction' => '']);
        $paginationType = PaginationTypeEnum::tryFrom($paginationParams['pagination_type'] ?? '') ?? PaginationTypeEnum::OFFSET;

        $booksQuery = $this->em->getRepository(Book::class)->searchBooks($searchQuery, $sortType);
        $searchResult = $this->paginationService->paginate($booksQuery, $paginationParams, $paginationType);

        return ApiView::create($searchResult->jsonSerialize(), Response::HTTP_OK);
    }
}
