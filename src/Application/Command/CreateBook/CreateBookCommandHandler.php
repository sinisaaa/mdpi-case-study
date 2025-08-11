<?php

declare(strict_types=1);

namespace App\Application\Command\CreateBook;

use App\DTO\AuthorDto;
use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateBookCommandHandler
{

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    /**
     * @param CreateBookCommand $command
     * @return Book
     */
    public function __invoke(CreateBookCommand $command): Book
    {
        $book = Book::create($command->title, $command->published, $command->isbn);

        $attached = [];
        foreach ($command->authors as $authorDto) {

            $key = $this->makeAuthorKey($authorDto);
            if (isset($attached[$key])) {
                continue;
            }

            $author = $this->em->getRepository(Author::class)->findOneByName($authorDto->firstname, $authorDto->lastname);
            if (null === $author) {
                $author = Author::create($authorDto->firstname, $authorDto->lastname, $authorDto->title);
                $this->em->persist($author);
            }

            $book->addAuthor($author);
            $attached[$key] = true;
        }

        $this->em->persist($book);
        $this->em->flush();

        return $book;
    }

    /**
     * @param AuthorDto $a
     * @return string
     */
    private function makeAuthorKey(AuthorDto $a): string
    {
        return sprintf('%s|%s',
            mb_strtolower(trim($a->firstname)),
            mb_strtolower(trim($a->lastname))
        );
    }

}
