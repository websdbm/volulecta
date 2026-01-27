<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class LoginAction
{
    public function __construct(
        private Twig $twig,
        private AuthService $authService
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            $email = $data['email'] ?? '';
            $password = $data['password'] ?? '';

            $user = $this->authService->authenticate($email, $password);

            if ($user) {
                // Start session and store user data
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_role'] = $user->getRole();

                // Role-based redirection
                $redirectUrl = match ($user->getRole()) {
                    'admin' => '/admin/dashboard',
                    'bibliophile' => '/bibliofilo/dashboard',
                    default => '/app/dashboard',
                };

                return $response->withHeader('Location', $redirectUrl)->withStatus(302);
            }

            return $this->render($response, ['error' => 'Email o password non validi']);
        }

        return $this->render($response);
    }

    private function render(Response $response, array $data = []): Response
    {
        return $this->twig->render($response, 'auth/login.twig', array_merge([
            'title' => 'Login',
        ], $data));
    }
}
