<?php

declare(strict_types=1);

namespace App\Service\Pagination\Result;

final class OffsetPaginationMeta implements PaginationMetaInterface
{

    /**
     * @param int $page
     * @param int $limit
     * @param int $total
     */
    public function __construct(
        public readonly int $page,
        public readonly int $limit,
        public readonly int $total
    ) {}

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'total' => $this->total,
        ];
    }
}
