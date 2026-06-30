<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Reorders a product's gallery. `ids` is the full set of the product's image
 * ids in the desired order; the first becomes the primary/cover image.
 */
class ProductImageOrderRequest
{
    /**
     * @param list<int> $ids
     */
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\All([new Assert\Type('integer'), new Assert\Positive()])]
        public array $ids = [],
    ) {
    }
}
