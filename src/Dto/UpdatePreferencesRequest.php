<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Incoming payload for PATCH /api/me/preferences. Same shape as the GET
 * response; any subset may be sent and is merged onto the stored preferences.
 */
readonly class UpdatePreferencesRequest
{
    public function __construct(
        #[Assert\Valid]
        public ?AppearancePreferencesRequest $appearance = null,
    ) {
    }
}
