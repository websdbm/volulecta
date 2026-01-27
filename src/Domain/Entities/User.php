<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use JsonSerializable;

class User implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $email,
        private string $passwordHash,
        private string $role = 'user',
        private string $status = 'active',
        private int $waitingList = 0,
        private ?string $emailVerifiedAt = null,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isWaitingList(): bool
    {
        return (bool) $this->waitingList;
    }

    public function getEmailVerifiedAt(): ?string
    {
        return $this->emailVerifiedAt;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'waitingList' => $this->waitingList,
            'emailVerifiedAt' => $this->emailVerifiedAt,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
