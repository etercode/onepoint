<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create/replace payload for a collection (admin). The slug is derived from the
 * name server-side.
 */
class CollectionWriteRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $name = '',

        #[Assert\Length(max: 255)]
        public ?string $tagline = null,

        #[Assert\Length(max: 500)]
        public ?string $image = null,

        public bool $featured = false,

        #[Assert\PositiveOrZero]
        public int $sortOrder = 0,
    ) {
    }
}
