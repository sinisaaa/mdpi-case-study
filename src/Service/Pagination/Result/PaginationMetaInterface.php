<?php

declare(strict_types=1);

namespace App\Service\Pagination\Result;

interface PaginationMetaInterface
{

    /**
     * @return array
     */
    public function jsonSerialize(): array;

}
