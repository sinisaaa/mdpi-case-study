<?php

declare(strict_types=1);

namespace App\Service\Pagination\Strategy;

use App\Service\Pagination\Result\PaginationResult;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;

final class CursorPaginationStrategy implements PaginationStrategyInterface
{

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @return PaginationResult
     */
    public function paginate(QueryBuilder $qb, array $params): PaginationResult
    {
        /* Cursor pagination will be better option for large datasets than using offset */
        throw new InvalidArgumentException('Not implemented');
    }
}
