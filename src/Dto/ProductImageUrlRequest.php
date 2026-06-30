<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Adds a gallery image by URL (for external/CDN images, as opposed to file
 * uploads). The value may also be a stored path relative to the uploads root.
 */
class ProductImageUrlRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        public string $url = '',
    ) {
    }
}
