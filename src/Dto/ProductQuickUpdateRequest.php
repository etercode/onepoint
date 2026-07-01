<?php

namespace App\Dto;

/**
 * Partial product update for inline admin list toggles. Only the provided
 * (non-null) fields are applied, so a caller can flip a single flag without
 * resending the whole product.
 */
class ProductQuickUpdateRequest
{
    public function __construct(
        public ?bool $inStock = null,
        public ?bool $isNew = null,
    ) {
    }
}
