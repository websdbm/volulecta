<?php

declare(strict_types=1);

namespace App\Application\Actions\App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardAction
{
    public function __construct(private Twig $twig)
    {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'app/dashboard.twig', [
            'title' => 'Dashboard',
        ]);
    }
}
