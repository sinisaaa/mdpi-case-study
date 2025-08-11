<?php

declare(strict_types=1);

namespace App\Service\Validation;

use App\Enum\BookSortTypeEnum;
use App\Service\Pagination\Enum\PaginationTypeEnum;
use Symfony\Component\HttpFoundation\Request;

final class BookSearchRequestValidator
{

    private const MAX_LIMIT = 100;

    /**
     * @param Request $request
     * @return array
     */
    public function validate(Request $request): array
    {
        $errors = [];
        $paginationParams = $request->query->all('pagination');
        $page = (int)($paginationParams['page'] ?? 1);
        $limit = (int)($paginationParams['limit'] ?? 10);

        if ($page < 1 || $limit < 1) {
            $errors[] = 'Invalid pagination parameters';
        }

        if ($limit > self::MAX_LIMIT) {
            $errors[] = 'Limit cannot be greater than ' . self::MAX_LIMIT;
        }

        $sortType = BookSortTypeEnum::tryFrom($request->query->get('sort') ?? '');
        if ($request->query->has('sort') && !$sortType) {
            $errors[] = 'Invalid sort type';
        }

        $paginationType = PaginationTypeEnum::tryFrom($paginationParams['pagination_type'] ?? '');
        if (isset($paginationParams['pagination_type']) && !$paginationType) {
            $errors[] = 'Invalid pagination type';
        }

        if (isset($paginationParams['cursor']) && !is_string($paginationParams['cursor'])) {
            $errors[] = 'Invalid cursor value';
        }

        if (isset($paginationParams['field']) && !is_string($paginationParams['field'])) {
            $errors[] = 'Invalid field value';
        }

        if (isset($paginationParams['direction'])) {
            $direction = strtolower($paginationParams['direction']);
            if (!in_array($direction, ['asc', 'desc'], true)) {
                $errors[] = 'Invalid direction value';
            }
        }

        return $errors;
    }
}
