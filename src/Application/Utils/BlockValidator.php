<?php

declare(strict_types=1);

namespace App\Application\Utils;

class BlockValidator
{
    // Font weight mapping - stessi valori del frontend
    private const FONT_WEIGHTS = [
        'roboto' => [300, 400, 500, 700],
        'poppins' => [300, 400, 500, 700],
        'inter' => [300, 400, 500, 700],
        'playfair' => [400, 700],
    ];

    /**
     * Valida i blocchi del builder
     * 
     * @param array $blocks Array di blocchi da validare
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateBlocks(array $blocks): array
    {
        $errors = [];

        foreach ($blocks as $index => $block) {
            $blockErrors = self::validateBlock($block, $index);
            if (!empty($blockErrors)) {
                $errors["block_$index"] = $blockErrors;
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Valida un singolo blocco
     */
    private static function validateBlock(array $block, int $index): array
    {
        $errors = [];

        // Validazione tipo blocco
        $validTypes = ['title', 'text', 'image', 'hero'];
        if (!isset($block['type']) || !in_array($block['type'], $validTypes)) {
            $errors[] = 'Tipo blocco non valido: ' . ($block['type'] ?? 'non specificato');
            return $errors;
        }

        // Validazione per tipo
        if (in_array($block['type'], ['title', 'text', 'image'])) {
            $errors = array_merge($errors, self::validateSimpleBlock($block));
        } elseif ($block['type'] === 'hero') {
            $errors = array_merge($errors, self::validateHeroBlock($block));
        }

        return $errors;
    }

    /**
     * Valida blocchi semplici (title, text, image)
     */
    private static function validateSimpleBlock(array $block): array
    {
        $errors = [];

        if (!isset($block['settings']) || !is_array($block['settings'])) {
            return ['Settings non trovate'];
        }

        foreach ($block['settings'] as $viewName => $settings) {
            if (!in_array($viewName, ['desktop', 'tablet', 'mobile'])) {
                continue; // ignora viste non valide
            }

            if (empty($settings)) {
                continue; // OK, stile nullable per tablet/mobile
            }

            // Valida font_family e font_weight
            if (isset($settings['font_family'])) {
                $fontFamily = $settings['font_family'];
                if (!isset(self::FONT_WEIGHTS[$fontFamily])) {
                    $errors[] = "$viewName: Font family non valido: $fontFamily";
                } elseif (isset($settings['font_weight'])) {
                    $fontWeight = (int) $settings['font_weight'];
                    $validWeights = self::FONT_WEIGHTS[$fontFamily];
                    if (!in_array($fontWeight, $validWeights)) {
                        $errors[] = "$viewName: Font weight '$fontWeight' non disponibile per $fontFamily. Validi: " . implode(', ', $validWeights);
                    }
                }
            }

            // Valida colori (devono essere hex validi)
            if (isset($settings['color']) && !self::isValidColor($settings['color'])) {
                $errors[] = "$viewName: Colore non valido: {$settings['color']}";
            }

            // Valida allineamento
            if (isset($settings['align'])) {
                $validAligns = ['left', 'center', 'right'];
                if (!in_array($settings['align'], $validAligns)) {
                    $errors[] = "$viewName: Allineamento non valido: {$settings['align']}";
                }
            }

            // Valida font size (deve essere numero + px)
            if (isset($settings['font_size']) && !self::isValidSize($settings['font_size'])) {
                $errors[] = "$viewName: Font size non valido: {$settings['font_size']}";
            }

            // Valida spacing values
            if (isset($settings['letter_spacing']) && !self::isValidSize($settings['letter_spacing'])) {
                $errors[] = "$viewName: Letter spacing non valido: {$settings['letter_spacing']}";
            }
            if (isset($settings['word_spacing']) && !self::isValidSize($settings['word_spacing'])) {
                $errors[] = "$viewName: Word spacing non valido: {$settings['word_spacing']}";
            }
        }

        return $errors;
    }

    /**
     * Valida blocchi hero
     */
    private static function validateHeroBlock(array $block): array
    {
        $errors = [];

        if (!isset($block['settings']) || !is_array($block['settings'])) {
            return ['Hero: Settings non trovate'];
        }

        foreach ($block['settings'] as $viewName => $settings) {
            if (!in_array($viewName, ['desktop', 'tablet', 'mobile'])) {
                continue;
            }

            if (empty($settings)) {
                continue;
            }

            // Valida font families per titolo, sottotitolo, bottone
            foreach (['title', 'subtitle', 'button'] as $part) {
                $fontFamilyKey = "{$part}_font_family";
                $fontWeightKey = "{$part}_font_weight";

                if (isset($settings[$fontFamilyKey])) {
                    $fontFamily = $settings[$fontFamilyKey];
                    if (!isset(self::FONT_WEIGHTS[$fontFamily])) {
                        $errors[] = "$viewName: $part - Font family non valido: $fontFamily";
                    } elseif (isset($settings[$fontWeightKey])) {
                        $fontWeight = (int) $settings[$fontWeightKey];
                        $validWeights = self::FONT_WEIGHTS[$fontFamily];
                        if (!in_array($fontWeight, $validWeights)) {
                            $errors[] = "$viewName: $part - Font weight '$fontWeight' non disponibile per $fontFamily";
                        }
                    }
                }

                // Valida colori
                $colorKey = "{$part}_color";
                if (isset($settings[$colorKey]) && !self::isValidColor($settings[$colorKey])) {
                    $errors[] = "$viewName: $part - Colore non valido: {$settings[$colorKey]}";
                }

                // Valida allineamento
                $alignKey = "{$part}_align";
                if (isset($settings[$alignKey])) {
                    $validAligns = ['left', 'center', 'right'];
                    if (!in_array($settings[$alignKey], $validAligns)) {
                        $errors[] = "$viewName: $part - Allineamento non valido: {$settings[$alignKey]}";
                    }
                }

                // Valida font size
                $fontSizeKey = "{$part}_font_size";
                if (isset($settings[$fontSizeKey]) && !self::isValidSize($settings[$fontSizeKey])) {
                    $errors[] = "$viewName: $part - Font size non valido: {$settings[$fontSizeKey]}";
                }
            }

            // Valida image position (hero specific)
            if (isset($settings['image_position'])) {
                $validPositions = ['left', 'right', 'top', 'bottom'];
                if (!in_array($settings['image_position'], $validPositions)) {
                    $errors[] = "$viewName: Image position non valida: {$settings['image_position']}";
                }
            }
        }

        return $errors;
    }

    /**
     * Verifica se un colore è un hex valido
     */
    private static function isValidColor(string $color): bool
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
    }

    /**
     * Verifica se una size è valida (numero + px)
     */
    private static function isValidSize(string $size): bool
    {
        return preg_match('/^\d+(\.\d+)?(px|rem|em)$/', $size) === 1;
    }
}
