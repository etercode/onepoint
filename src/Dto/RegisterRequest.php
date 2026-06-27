<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Incoming payload for POST /api/register. Deserialized and validated
 * automatically via #[MapRequestPayload] (returns 422 on failure).
 */
readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $password = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 3, max: 180)]
        public string $username = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $name = '',

        #[Assert\NotBlank]
        #[Assert\Length(max: 100)]
        public string $lastname = '',

        #[Assert\Length(max: 255)]
        public ?string $profilePhoto = null,

        #[Assert\Date(message: 'birthday must be a valid date in YYYY-MM-DD format.')]
        public ?string $birthday = null,

        public ?string $description = null,

        #[Assert\Length(max: 255)]
        public ?string $statusText = null,
    ) {
    }
}
