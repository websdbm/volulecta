<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\Repositories\UserRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ListUsersAction
{
    public function __construct(
        private Twig $twig,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $users = $this->userRepository->findAll();

        return $this->twig->render($response, 'admin/user_list.twig', [
            'title' => 'Gestione Utenti',
            'users' => $users,
        ]);
    }
}
