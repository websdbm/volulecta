<?php

/**
 * BlockValidator - Unit Tests
 * 
 * Testa la validazione dei blocchi del builder
 */

// Autoload Composer
require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Utils\BlockValidator;

class BlockValidatorTest
{
    private $passed = 0;
    private $failed = 0;
    private $results = [];

    public function run()
    {
        echo "\n🧪 BlockValidator Unit Tests\n";
        echo str_repeat("=", 60) . "\n\n";

        // Test 1: Validazione blocco valido
        $this->testValidBlocks();
        
        // Test 2: Validazione font weight
        $this->testFontWeightValidation();
        
        // Test 3: Validazione colori
        $this->testColorValidation();
        
        // Test 4: Validazione font size
        $this->testFontSizeValidation();
        
        // Test 5: Validazione allineamento
        $this->testAlignmentValidation();
        
        // Test 6: Validazione hero block
        $this->testHeroBlockValidation();
        
        // Riepilogo
        $this->printSummary();
    }

    private function testValidBlocks()
    {
        echo "📦 Test 1: BLOCCHI VALIDI\n\n";

        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test Title'],
                'settings' => [
                    'desktop' => [
                        'font_family' => 'roboto',
                        'font_weight' => '700',
                        'font_size' => '32px',
                        'color' => '#000000',
                        'align' => 'left',
                        'padding' => '10px'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];

        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Blocco title valido');
        $this->assert(empty($validation['errors']), 'Nessun errore');
    }

    private function testFontWeightValidation()
    {
        echo "\n🎨 Test 2: VALIDAZIONE FONT WEIGHT\n\n";

        // Test 1: Roboto con weight 400 (valido)
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_family' => 'roboto',
                        'font_weight' => '400'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Roboto con weight 400 valido');

        // Test 2: Playfair con weight 300 (non valido - Playfair ha solo 400, 700)
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_family' => 'playfair',
                        'font_weight' => '300'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], 'Playfair con weight 300 non valido');
        $this->assert(!empty($validation['errors']), 'Errore generato');

        // Test 3: Font family non valida
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_family' => 'helvetica',
                        'font_weight' => '400'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], 'Font family non valida genera errore');
    }

    private function testColorValidation()
    {
        echo "\n🎨 Test 3: VALIDAZIONE COLORI\n\n";

        // Test 1: Colore hex valido
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'color' => '#FF5500'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Colore hex #FF5500 valido');

        // Test 2: Colore non hex (non valido)
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'color' => 'red'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], 'Colore "red" non valido');

        // Test 3: Colore hex maiuscolo
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'color' => '#000000'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Colore hex minuscolo #000000 valido');
    }

    private function testFontSizeValidation()
    {
        echo "\n📐 Test 4: VALIDAZIONE FONT SIZE\n\n";

        // Test 1: Font size valido
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_size' => '32px'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Font size 32px valido');

        // Test 2: Font size decimale
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_size' => '1.5rem'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Font size 1.5rem valido');

        // Test 3: Font size non valido
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => [
                        'font_size' => 'large'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], 'Font size "large" non valido');
    }

    private function testAlignmentValidation()
    {
        echo "\n📏 Test 5: VALIDAZIONE ALLINEAMENTO\n\n";

        foreach (['left', 'center', 'right'] as $align) {
            $blocks = [
                [
                    'type' => 'title',
                    'content' => ['text' => 'Test'],
                    'settings' => [
                        'desktop' => ['align' => $align],
                        'tablet' => [],
                        'mobile' => []
                    ]
                ]
            ];
            $validation = BlockValidator::validateBlocks($blocks);
            $this->assert($validation['valid'], "Allineamento '$align' valido");
        }

        // Test allineamento non valido
        $blocks = [
            [
                'type' => 'title',
                'content' => ['text' => 'Test'],
                'settings' => [
                    'desktop' => ['align' => 'invalid'],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], "Allineamento 'invalid' non valido");
    }

    private function testHeroBlockValidation()
    {
        echo "\n🦸 Test 6: VALIDAZIONE HERO BLOCK\n\n";

        $blocks = [
            [
                'type' => 'hero',
                'content' => [
                    'title' => 'Hero Title',
                    'subtitle' => 'Hero Subtitle',
                    'buttonText' => 'Click',
                    'buttonLink' => '/path',
                    'imageUrl' => 'https://example.com/img.jpg',
                    'imagePosition' => 'left'
                ],
                'settings' => [
                    'desktop' => [
                        'title_font_family' => 'roboto',
                        'title_font_weight' => '700',
                        'title_font_size' => '48px',
                        'title_color' => '#000000',
                        'title_align' => 'left',
                        'subtitle_font_family' => 'roboto',
                        'subtitle_font_weight' => '400',
                        'subtitle_font_size' => '18px',
                        'subtitle_color' => '#666666',
                        'button_font_family' => 'roboto',
                        'button_font_weight' => '700',
                        'button_font_size' => '16px',
                        'button_color' => '#ffffff',
                        'button_bg_color' => '#ff9800',
                        'image_position' => 'left'
                    ],
                    'tablet' => [],
                    'mobile' => []
                ]
            ]
        ];

        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert($validation['valid'], 'Hero block valido');

        // Test con image position non valida
        $blocks[0]['settings']['desktop']['image_position'] = 'diagonal';
        $validation = BlockValidator::validateBlocks($blocks);
        $this->assert(!$validation['valid'], 'Hero block con image_position non valida');
    }

    private function assert($condition, $testName)
    {
        if ($condition) {
            $this->passed++;
            $this->results[] = ['test' => $testName, 'status' => '✅ PASS'];
            echo "✅ $testName\n";
        } else {
            $this->failed++;
            $this->results[] = ['test' => $testName, 'status' => '❌ FAIL'];
            echo "❌ $testName\n";
        }
    }

    private function printSummary()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "\n📊 RISULTATI TEST\n\n";
        echo "✅ Test Passati: {$this->passed}\n";
        echo "❌ Test Falliti: {$this->failed}\n";
        
        $total = $this->passed + $this->failed;
        $rate = $total > 0 ? (($this->passed / $total) * 100) : 0;
        echo "📈 Success Rate: " . number_format($rate, 1) . "%\n\n";

        echo str_repeat("=", 60) . "\n";

        if ($this->failed === 0) {
            echo "\n🎉 TUTTI I TEST PASSATI!\n";
            echo "✅ BlockValidator è pronto per la produzione\n\n";
        } else {
            echo "\n⚠️ {$this->failed} TEST FALLITO/I\n\n";
        }
    }
}

// Esegui i test
$tester = new BlockValidatorTest();
$tester->run();
