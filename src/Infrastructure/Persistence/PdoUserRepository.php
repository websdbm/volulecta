<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use PDO;

class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $db)
    {
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();

        if (!$data) {
            return null;
        }

        return $this->mapToEntity($data);
    }

    public function save(User $user): User
    {
        if ($user->getId() === null) {
            return $this->insert($user);
        }

        return $this->update($user);
    }

    private function insert(User $user): User
    {
        $sql = 'INSERT INTO users (email, password_hash, role, status, waiting_list, email_verified_at, created_at, updated_at) 
                VALUES (:email, :password_hash, :role, :status, :waiting_list, :email_verified_at, NOW(), NOW())';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole(),
            'status' => $user->getStatus(),
            'waiting_list' => $user->isWaitingList() ? 1 : 0,
            'email_verified_at' => $user->getEmailVerifiedAt(),
        ]);

        $id = (int) $this->db->lastInsertId();
        return $this->findById($id);
    }

    private function update(User $user): User
    {
        $sql = 'UPDATE users SET 
                email = :email, 
                password_hash = :password_hash, 
                role = :role, 
                status = :status, 
                waiting_list = :waiting_list, 
                email_verified_at = :email_verified_at, 
                updated_at = NOW() 
                WHERE id = :id';
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password_hash' => $user->getPasswordHash(),
            'role' => $user->getRole(),
            'status' => $user->getStatus(),
            'waiting_list' => $user->isWaitingList() ? 1 : 0,
            'email_verified_at' => $user->getEmailVerifiedAt(),
        ]);

        return $this->findById($user->getId());
    }

    private function mapToEntity(array $data): User
    {
        return new User(
            (int) $data['id'],
            $data['email'],
            $data['password_hash'],
            $data['role'],
            $data['status'],
            (int) $data['waiting_list'],
            $data['email_verified_at'],
            $data['created_at'],
            $data['updated_at']
        );
    }
}
