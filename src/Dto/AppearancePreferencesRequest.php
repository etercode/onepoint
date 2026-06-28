<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Appearance (client-side theming) preferences. Every field is optional so a
 * PATCH can carry a partial object; a provided value must be one of the allowed
 * choices (Assert\Choice skips nulls, giving true partial-update semantics).
 * The allowed-value lists are the single source of truth for validation.
 */
readonly class AppearancePreferencesRequest
{
    public const THEMES = ['light', 'dark', 'dim'];
    public const ACCENTS = ['blue', 'facebook', 'purple', 'green', 'rose'];
    public const FONT_FAMILIES = ['system', 'classic', 'mono'];
    public const FONT_SIZES = ['sm', 'md', 'lg'];

    public function __construct(
        #[Assert\Choice(choices: self::THEMES)]
        public ?string $theme = null,

        #[Assert\Choice(choices: self::ACCENTS)]
        public ?string $accent = null,

        #[Assert\Choice(choices: self::FONT_FAMILIES)]
        public ?string $fontFamily = null,

        #[Assert\Choice(choices: self::FONT_SIZES)]
        public ?string $fontSize = null,
    ) {
    }
}
