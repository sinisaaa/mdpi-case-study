<?php

declare(strict_types=1);

namespace App\Helpers;

final class BookHelper
{

    /**
     * @param string $isbn
     * @param bool $addDashes
     * @return string
     */
    public static function formatISBN(string $isbn, bool $addDashes = false): string
    {
        if (empty($isbn)) {
            return '';
        }

        $isbn = str_replace('-', '', $isbn);
        $length = strlen($isbn);

        if ($addDashes) {
            if ($length === 10) {
                // X-XXX-XXXXX-X
                return sprintf('%s-%s-%s-%s',
                    substr($isbn, 0, 1),
                    substr($isbn, 1, 3),
                    substr($isbn, 4, 5),
                    substr($isbn, 9, 1)
                );
            }

            if ($length === 13) {
                // XXX-X-XXXXX-XXX
                return sprintf('%s-%s-%s-%s',
                    substr($isbn, 0, 3),
                    substr($isbn, 3, 1),
                    substr($isbn, 4, 5),
                    substr($isbn, 9, 4)
                );
            }

            return '';
        }

        if ($length === 10 || $length === 13) {
            return $isbn;
        }

        return '';
    }

}
