<?php

declare(strict_types=1);

namespace App\Service\Pagination;

use App\Service\Pagination\Enum\PaginationTypeEnum;
use App\Service\Pagination\Result\PaginationResult;
use App\Service\Pagination\Strategy\CursorPaginationStrategy;
use App\Service\Pagination\Strategy\OffsetPaginationStrategy;
use Doctrine\ORM\QueryBuilder;

final class PaginationService
{

    /**
     * @param OffsetPaginationStrategy $offsetStrategy
     * @param CursorPaginationStrategy $cursorStrategy
     */
    public function __construct(
        private readonly OffsetPaginationStrategy $offsetStrategy,
        private readonly CursorPaginationStrategy $cursorStrategy,
    ) {}

    /**
     * @param QueryBuilder $qb
     * @param array $params
     * @param PaginationTypeEnum $type
     * @return PaginationResult
     */
    public function paginate(QueryBuilder $qb, array $params, PaginationTypeEnum $type = PaginationTypeEnum::OFFSET): PaginationResult
    {
        return match ($type) {
            PaginationTypeEnum::OFFSET => $this->offsetStrategy->paginate($qb, $params),
            PaginationTypeEnum::CURSOR => $this->cursorStrategy->paginate($qb, $params),
            default => throw new \InvalidArgumentException("Unknown pagination type: $type->value"),
        };
    }
}
