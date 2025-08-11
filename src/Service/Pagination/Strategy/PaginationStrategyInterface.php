<?php

namespace App\Service\Pagination\Strategy;

use App\Service\Pagination\Result\PaginationResult;
use Doctrine\ORM\QueryBuilder;

interface PaginationStrategyInterface
{

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @return PaginationResult
     */
    public function paginate(QueryBuilder $qb, array $params): PaginationResult;

}
