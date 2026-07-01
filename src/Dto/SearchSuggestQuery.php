<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query for the storefront search autocomplete. `q` may be empty (the endpoint
 * then returns no results); `limit` caps the number of suggestions.
 */
class SearchSuggestQuery
{
    public function __construct(
        #[Assert\Length(max: 120)]
        public string $q = '',

        #[Assert\Range(min: 1, max: 50)]
        public int $limit = 6,
    ) {
    }
}
