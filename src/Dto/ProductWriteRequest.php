<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create/replace payload for a product (admin). PUT and POST send the full set
 * of editable scalar fields. The image gallery is managed separately through
 * the /api/admin/products/{id}/images endpoints.
 */
class ProductWriteRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 180)]
        public string $name = '',

        #[Assert\NotNull]
        #[Assert\Positive]
        public ?int $price = null,

        #[Assert\NotNull]
        #[Assert\Positive]
        public ?int $categoryId = null,

        #[Assert\NotNull]
        #[Assert\Positive]
        public ?int $collectionId = null,

        #[Assert\PositiveOrZero]
        public ?int $oldPrice = null,

        public bool $onSale = false,

        public bool $isNew = false,

        public bool $inStock = true,

        public bool $freeDelivery = true,

        #[Assert\PositiveOrZero]
        #[Assert\LessThanOrEqual(99)]
        public int $warrantyYears = 2,

        #[Assert\Length(max: 180)]
        public ?string $material = null,

        #[Assert\Length(max: 120)]
        public ?string $color = null,

        #[Assert\Length(max: 120)]
        public ?string $dimensions = null,

        public ?string $description = null,
    ) {
    }
}
