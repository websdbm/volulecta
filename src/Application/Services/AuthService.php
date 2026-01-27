<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entities\User;
use App\Domain\Repositories\UserRepositoryInterface;
use Exception;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function register(string $email, string $password): User
    {
        if ($this->userRepository->findByEmail($email)) {
            throw new Exception('Email already registered');
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $user = new User(null, $email, $passwordHash);

        return $this->userRepository->save($user);
    }

    public function authenticate(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user->getPasswordHash())) {
            return null;
        }

        return $user;
    }
}
