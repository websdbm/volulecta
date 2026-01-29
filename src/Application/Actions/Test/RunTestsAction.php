<?php

declare(strict_types=1);

namespace App\Application\Actions\Test;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;

class RunTestsAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        ob_start();
        
        // Includi il test file
        require_once __DIR__ . '/../../../tests/BlockValidatorTest.php';
        
        // Cattura l'output
        $output = ob_get_clean();
        
        // Parse il risultato dal test
        // Cerca i numeri nel formato "✅ Test Passati: X"
        preg_match('/✅ Test Passati: (\d+)/', $output, $passedMatch);
        preg_match('/❌ Test Falliti: (\d+)/', $output, $failedMatch);
        
        $passed = isset($passedMatch[1]) ? (int) $passedMatch[1] : 0;
        $failed = isset($failedMatch[1]) ? (int) $failedMatch[1] : 0;
        $total = $passed + $failed;
        $successRate = $total > 0 ? (($passed / $total) * 100) : 0;
        
        return $response
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(
                \Slim\Psr7\Factory\StreamFactory::create(json_encode([
                    'status' => 'success',
                    'passed' => $passed,
                    'failed' => $failed,
                    'total' => $total,
                    'successRate' => round($successRate, 1),
                    'output' => $output
                ]))
            );
    }
}
