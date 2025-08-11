<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Enum\BookSortTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Caching is not possible for this query because its result depends on user input.
     * Indexes won't work because we're using LIKE operator from both ends.
     * Probably the best solution is to implement this with elasticsearch and full-text search.
     *
     * @param string|null $queryString
     * @param BookSortTypeEnum|null $sort
     * @return QueryBuilder
     */
    public function searchBooks(?string $queryString, ?BookSortTypeEnum $sort): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b')
            ->addSelect('a')
            ->leftJoin('b.authors', 'a');

        if (null !== $queryString) {
            $term = trim($queryString);
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(b.title) LIKE :term',
                    'LOWER(a.firstName) LIKE :term',
                    'LOWER(a.lastName) LIKE :term'
                )
            )->setParameter('term', '%' . strtolower($term) . '%');
        }

        if (null !== $sort) {
            match ($sort) {
                BookSortTypeEnum::TITLE_ASC => $qb->orderBy("b.title", 'ASC'),
                BookSortTypeEnum::TITLE_DESC => $qb->orderBy("b.title", 'DESC'),
                BookSortTypeEnum::PUBLISHED_DESC => $qb->orderBy("b.publishedAt", 'DESC'),
                BookSortTypeEnum::PUBLISHED_ASC => $qb->orderBy("b.publishedAt", 'ASC'),
                default => $qb->orderBy("b.id", 'ASC')
            };
        }

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function findAllQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('b')
            ->orderBy('b.title', 'ASC');
    }

    /**
     * @param string $isbn
     * @return Book|null
     */
    public function findByIsbn(string $isbn): ?Book
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.isbn = :isbn')
            ->setParameter('isbn', $isbn)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
