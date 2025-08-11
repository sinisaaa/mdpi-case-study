<?php

declare(strict_types=1);

namespace App\Service\Pagination\Enum;

enum PaginationTypeEnum: string
{

    case OFFSET = 'offset';
    case CURSOR = 'cursor';

}
