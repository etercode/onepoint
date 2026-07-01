<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Admin payload for the editable catalog mega-menu text.
 */
class CatalogMenuWriteRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 120)]
        public string $heading = '',

        #[Assert\Length(max: 200)]
        public string $subheading = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 60)]
        public string $buttonLabel = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 200)]
        public string $buttonHref = '',
    ) {
    }
}
