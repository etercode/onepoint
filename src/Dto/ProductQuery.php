<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query-string filters for GET /api/products. Every field is optional; absent
 * fields mean "no constraint". Booleans accept 1/0/true/false.
 */
class ProductQuery
{
    public const SORTS = ['default', 'price_asc', 'price_desc', 'newest', 'name'];

    public function __construct(
        /** Category slug. */
        public ?string $category = null,

        /** Collection slug. */
        public ?string $collection = null,

        /** Free-text search across name, description, material, category, collection. */
        public ?string $q = null,

        /** Comma-separated product ids (e.g. "3,7,12"); used to hydrate the cart. */
        public ?string $ids = null,

        public ?bool $isNew = null,

        public ?bool $onSale = null,

        public ?bool $inStock = null,

        #[Assert\PositiveOrZero]
        public ?int $minPrice = null,

        #[Assert\PositiveOrZero]
        public ?int $maxPrice = null,

        #[Assert\Choice(choices: self::SORTS)]
        public string $sort = 'default',

        #[Assert\Range(min: 1, max: 100)]
        public ?int $limit = null,

        #[Assert\PositiveOrZero]
        public int $offset = 0,
    ) {
    }
}
