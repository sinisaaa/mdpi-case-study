<?php

declare(strict_types=1);

namespace App\Application\Command\MigrateAuthors;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MigrateAuthorsCommandHandler
{

    /** @var int  */
    private int $batchSize = 100;

    /** @var array  */
    private array $authorCache = [];

    /** @var int  */
    private int $created = 0;

    /** @var int  */
    private int $linked = 0;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function __invoke(MigrateAuthorsCommand $command): array
    {
        /**
         * @var int $index
         * @var Book $book
         */
        foreach ($this->getBooksWithAuthorString() as $index => $book) {
            $authorName = $book->getAuthor();

            if (null === $authorName) {
                continue;
            }

            [$firstName, $lastName] = $this->parseFullName($authorName);

            $cacheKey = strtolower($firstName . ' ' . $lastName);

            $author = $this->getAuthorFromCache($cacheKey) ?? $this->getAuthorFromDatabase($firstName, $lastName);

            if (null === $author) {
                $author =$this->createAuthor($firstName, $lastName);
                $this->authorCache[$cacheKey] = $author;
            }

            $this->linkAuthorToBook($book, $author);

            if (($index + 1) % $this->batchSize === 0) {
                $this->flushAndClear();
            }

        }

        $this->em->flush();

        return [
            'created' => $this->created,
            'linked' => $this->linked,
        ];
    }

    /**
     * @return iterable
     */
    private function getBooksWithAuthorString(): iterable
    {
        return $this->em->getRepository(Book::class)
            ->createQueryBuilder('b')
            ->where('b.author IS NOT NULL')
            ->getQuery()
            ->toIterable();
    }


    /**
     * @param string $fullName
     * @return array
     */
    private function parseFullName(string $fullName): array
    {
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0];
        $lastName = $parts[1] ?? '';

        return [$firstName, $lastName];
    }


    /**
     * @param string $firstName
     * @param string $lastName
     * @return Author|null
     */
    private function getAuthorFromDatabase(string $firstName, string $lastName): ?Author
    {
        return $this->em->getRepository(Author::class)->findOneBy([
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);
    }

    /**
     * @param string $cacheKey
     * @return Author|null
     */
    private function getAuthorFromCache(string $cacheKey): ?Author
    {
        return $this->authorCache[$cacheKey] ?? null;
    }

    /**
     * @param string $firstName
     * @param string $lastName
     * @return Author
     */
    private function createAuthor(string $firstName, string $lastName): Author
    {
        $author = Author::create($firstName, $lastName);
        $this->em->persist($author);
        $this->created++;

        return $author;
    }

    /**
     * @param Book $book
     * @param Author $author
     * @return void
     */
    private function linkAuthorToBook(Book $book, Author $author): void
    {
        $book->addAuthor($author);
        $book->setAuthor(null);
        $this->linked++;
    }

    /**
     * @return void
     */
    private function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();

        $this->authorCache = [];
    }

}
