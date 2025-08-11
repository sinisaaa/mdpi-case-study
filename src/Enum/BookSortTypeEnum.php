<?php

declare(strict_types=1);

namespace App\Enum;

enum BookSortTypeEnum: string
{

    case TITLE_ASC = 'az';
    case TITLE_DESC = 'za';
    case PUBLISHED_ASC = 'oldest';
    case PUBLISHED_DESC = 'newest';

}
