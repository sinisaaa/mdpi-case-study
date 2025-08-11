<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class AuthorDto
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 10)]
    public string $title = 'Unknown';

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $firstname;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    public string $lastname;
}
