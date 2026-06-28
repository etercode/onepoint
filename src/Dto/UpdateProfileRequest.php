<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Incoming payload for PATCH /api/me. Deserialized and validated automatically
 * via #[MapRequestPayload] (returns 422 on failure). Treated as a full
 * replacement of the editable profile fields.
 */
readonly class UpdateProfileRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 180)]
        public string $username = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $lastname = '',

        #[Assert\Timezone]
        public ?string $timezone = null,

        #[Assert\Language]
        public ?string $language = null,

        #[Assert\Date(message: 'birthday must be a valid date in YYYY-MM-DD format.')]
        public ?string $birthday = null,

        public ?string $description = null,

        #[Assert\Length(max: 255)]
        public ?string $statusText = null,
    ) {
    }
}
