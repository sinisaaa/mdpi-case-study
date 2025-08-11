<?php

declare(strict_types=1);

namespace App\Service\Pagination\Strategy;

use App\Service\Pagination\Result\OffsetPaginationMeta;
use App\Service\Pagination\Result\PaginationResult;
use Doctrine\ORM\QueryBuilder;

final class OffsetPaginationStrategy implements PaginationStrategyInterface
{

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @return PaginationResult
     */
    public function paginate(QueryBuilder $qb, array $params): PaginationResult
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $limit = max(1, (int)($params['limit'] ?? 10));
        $offset = ($page - 1) * $limit;

        $countQb = clone $qb;
        $total = (int) $countQb
            ->resetDQLPart('orderBy')
            ->select('COUNT(DISTINCT b.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();

        return new PaginationResult($items, new OffsetPaginationMeta($page, $limit, $total));
    }

}
