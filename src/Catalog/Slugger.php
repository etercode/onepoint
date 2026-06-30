<?php

namespace App\Catalog;

/**
 * URL slug generator. Mirrors the frontend's slugify() (Azerbaijani
 * transliteration) byte-for-byte so the slugs stored here match the links the
 * SvelteKit storefront builds with slugify(name).
 */
final class Slugger
{
    private const TRANSLITERATE = [
        'ə' => 'e', 'ı' => 'i', 'ö' => 'o', 'ü' => 'u',
        'ş' => 's', 'ç' => 'c', 'ğ' => 'g',
    ];

    public static function slugify(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = strtr($text, self::TRANSLITERATE);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';

        return trim($text, '-');
    }
}
