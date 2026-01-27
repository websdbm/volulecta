<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;
use Exception;

class RegisterAction
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
            $confirmPassword = $data['confirm_password'] ?? '';

            if ($password !== $confirmPassword) {
                return $this->render($response, ['error' => 'Le password non coincidono']);
            }

            try {
                $this->authService->register($email, $password);
                return $response->withHeader('Location', '/login')->withStatus(302);
            } catch (Exception $e) {
                return $this->render($response, ['error' => $e->getMessage()]);
            }
        }

        return $this->render($response);
    }

    private function render(Response $response, array $data = []): Response
    {
        return $this->twig->render($response, 'auth/register.twig', array_merge([
            'title' => 'Registrazione',
        ], $data));
    }
}
