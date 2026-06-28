<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query parameters for GET /api/username/available. Validated automatically via
 * #[MapQueryString] (returns 422 when the username is missing or malformed).
 */
readonly class CheckUsernameQuery
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 180)]
        public string $username = '',
    ) {
    }
}
