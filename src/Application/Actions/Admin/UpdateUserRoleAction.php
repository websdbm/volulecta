<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Domain\Repositories\UserRepositoryInterface;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UpdateUserRoleAction
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PDO $db
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $userId = (int) $args['id'];
        $data = $request->getParsedBody();
        $newRole = $data['role'] ?? null;

        if (!$newRole || !in_array($newRole, ['admin', 'bibliophile', 'user'])) {
            return $response->withStatus(400);
        }

        $user = $this->userRepository->findById($userId);
        if (!$user) {
            return $response->withStatus(404);
        }

        $user = $user->withRole($newRole);
        $this->userRepository->save($user);

        // If promoted to bibliophile, ensure a profile exists
        if ($newRole === 'bibliophile') {
            $stmt = $this->db->prepare('INSERT IGNORE INTO bibliophile_profiles (user_id, display_name, created_at, updated_at) VALUES (:user_id, :display_name, NOW(), NOW())');
            $stmt->execute([
                'user_id' => $user->getId(),
                'display_name' => explode('@', $user->getEmail())[0],
            ]);
        }

        return $response->withHeader('Location', '/admin/users')->withStatus(302);
    }
}
