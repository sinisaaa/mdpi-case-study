<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

class CreateBookDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title;

    /** @var AuthorDto[] */
    #[Assert\NotBlank]
    #[Assert\Count(min: 1)]
    #[Assert\Valid]
    public array $authors = [];

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public ?\DateTime $published = null;

    #[Assert\Length(max: 20)]
    public ?string $isbn = null;
}
