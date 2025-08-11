<?php

declare(strict_types=1);

namespace App\Application\Command\CreateBook;

use App\DTO\AuthorDto;

final class CreateBookCommand
{

    /**
     * @param string $title
     * @param AuthorDto[] $authors
     * @param \DateTime|null $published
     * @param string|null $isbn
     */
    public function __construct(
        public readonly string $title,
        public readonly array $authors,
        public readonly ?\DateTime $published,
        public readonly ?string $isbn
    )
    {
    }

}
