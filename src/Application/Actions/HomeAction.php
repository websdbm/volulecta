<?php

declare(strict_types=1);

namespace App\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class HomeAction
{
    private Twig $twig;

    public function __construct(Twig $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $role = $_SESSION['user_role'] ?? 'user';
            $redirectUrl = match ($role) {
                'admin' => '/admin/dashboard',
                'bibliophile' => '/bibliofilo/dashboard',
                default => '/app/dashboard',
            };
            return $response->withHeader('Location', $redirectUrl)->withStatus(302);
        }

        return $this->twig->render($response, 'home.twig', [
            'title' => 'Benvenuto in Volulecta',
            'description' => 'Il tuo servizio di raccomandazioni librarie personalizzate',
        ]);
    }
}
