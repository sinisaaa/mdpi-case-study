<?php

declare(strict_types=1);

namespace App\Service\Pagination\Result;

final class PaginationResult
{

    /**
     * @param array $items
     * @param PaginationMetaInterface $meta
     */
    public function __construct(
        public readonly array $items,
        public readonly PaginationMetaInterface $meta
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'meta' => $this->meta->jsonSerialize(),
        ];
    }
}
