<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use JsonSerializable;

class ApiKey implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $keyName,
        private string $keyLabel,
        private string $keyValue,
        private ?string $description,
        private bool $isActive = true,
        private string $createdAt = '',
        private string $updatedAt = ''
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyName(): string
    {
        return $this->keyName;
    }

    public function getKeyLabel(): string
    {
        return $this->keyLabel;
    }

    public function getKeyValue(): string
    {
        return $this->keyValue;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'key_name' => $this->keyName,
            'key_label' => $this->keyLabel,
            'key_value' => $this->keyValue,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
