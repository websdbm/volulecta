<?php

declare(strict_types=1);

namespace App\Application\Utils;

class GoogleFonts
{
    /**
     * Font di Google Fonts disponibili per il builder CMS
     * Questi devono essere caricati nel template
     */
    public const FONTS = [
        'roboto' => [
            'name' => 'Roboto',
            'family' => 'Roboto, sans-serif',
            'weights' => [300, 400, 500, 700],
            'url' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap'
        ],
        'poppins' => [
            'name' => 'Poppins',
            'family' => 'Poppins, sans-serif',
            'weights' => [300, 400, 500, 700],
            'url' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;700&display=swap'
        ],
        'inter' => [
            'name' => 'Inter',
            'family' => 'Inter, sans-serif',
            'weights' => [300, 400, 500, 700],
            'url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&display=swap'
        ],
        'playfair' => [
            'name' => 'Playfair Display',
            'family' => '"Playfair Display", serif',
            'weights' => [400, 700],
            'url' => 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&display=swap'
        ],
    ];

    public static function getFontList(): array
    {
        return self::FONTS;
    }

    public static function getFontUrl(string $fontKey): ?string
    {
        return self::FONTS[$fontKey]['url'] ?? null;
    }

    public static function getFontFamily(string $fontKey): ?string
    {
        return self::FONTS[$fontKey]['family'] ?? null;
    }

    public static function getAvailableFonts(): array
    {
        $fonts = [];
        foreach (self::FONTS as $key => $font) {
            $fonts[$key] = $font['name'];
        }
        return $fonts;
    }
}
