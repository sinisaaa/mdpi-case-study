<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    /**
     * @param string $first
     * @param string $last
     * @return Author|null
     */
    public function findOneByName(string $first, string $last): ?Author
    {
        return $this->createQueryBuilder('a')
            ->andWhere('LOWER(TRIM(a.firstName)) = :first')
            ->andWhere('LOWER(TRIM(a.lastName))  = :last')
            ->setParameter('first', mb_strtolower(trim($first)))
            ->setParameter('last',  mb_strtolower(trim($last)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
