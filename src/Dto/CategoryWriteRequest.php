<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create/replace payload for a category (admin). The slug is derived from the
 * name server-side.
 */
class CategoryWriteRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',

        #[Assert\Length(max: 500)]
        public ?string $image = null,

        #[Assert\PositiveOrZero]
        public int $sortOrder = 0,
    ) {
    }
}
