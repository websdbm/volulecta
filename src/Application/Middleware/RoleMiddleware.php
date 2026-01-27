<?php

declare(strict_types=1);

namespace App\Application\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * @param string[]|string $allowedRoles
     */
    public function __construct(private array|string $allowedRoles)
    {
        if (is_string($this->allowedRoles)) {
            $this->allowedRoles = [$this->allowedRoles];
        }
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            $response = new SlimResponse();
            return $response->withHeader('Location', '/login')->withStatus(302);
        }

        $userRole = $_SESSION['user_role'] ?? null;

        if (!$userRole || !in_array($userRole, $this->allowedRoles, true)) {
            $response = new SlimResponse();
            $response->getBody()->write('Access Denied: You do not have the required permissions.');
            return $response->withStatus(403);
        }

        return $handler->handle($request);
    }
}

