<?php

declare(strict_types=1);

namespace App\Service\Pagination\Result;

final class CursorPaginationMeta implements PaginationMetaInterface
{

    /**
     * @param string|null $nextCursor
     * @param bool $hasNextPage
     */
    public function __construct(
        public readonly ?string $nextCursor,
        public readonly bool $hasNextPage
    ) {}


    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'nextCursor' => $this->nextCursor,
            'hasNextPage' => $this->hasNextPage,
        ];
    }
}
