<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Incoming payload for POST /api/me/password. The current password is verified
 * in the controller (not a validation concern); format is validated here.
 */
readonly class ChangePasswordRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $currentPassword = '',

        #[Assert\NotBlank]
        #[Assert\Length(min: 8, max: 4096)]
        public string $newPassword = '',
    ) {
    }
}
