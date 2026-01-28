<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\ApiKey;
use App\Domain\Repositories\ApiKeyRepositoryInterface;
use PDO;

class PdoApiKeyRepository implements ApiKeyRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM api_keys ORDER BY key_label ASC');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(fn($row) => $this->mapToEntity($row), $rows);
    }

    public function findById(int $id): ?ApiKey
    {
        $stmt = $this->pdo->prepare('SELECT * FROM api_keys WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapToEntity($row) : null;
    }

    public function findByName(string $keyName): ?ApiKey
    {
        $stmt = $this->pdo->prepare('SELECT * FROM api_keys WHERE key_name = :key_name');
        $stmt->execute(['key_name' => $keyName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ? $this->mapToEntity($row) : null;
    }

    public function save(ApiKey $apiKey): void
    {
        if ($apiKey->getId()) {
            // Update
            $stmt = $this->pdo->prepare('
                UPDATE api_keys 
                SET key_name = :key_name, 
                    key_label = :key_label, 
                    key_value = :key_value, 
                    description = :description, 
                    is_active = :is_active,
                    updated_at = NOW()
                WHERE id = :id
            ');
            $stmt->execute([
                'id' => $apiKey->getId(),
                'key_name' => $apiKey->getKeyName(),
                'key_label' => $apiKey->getKeyLabel(),
                'key_value' => $apiKey->getKeyValue(),
                'description' => $apiKey->getDescription(),
                'is_active' => $apiKey->isActive() ? 1 : 0,
            ]);
        } else {
            // Insert
            $stmt = $this->pdo->prepare('
                INSERT INTO api_keys (key_name, key_label, key_value, description, is_active, created_at, updated_at) 
                VALUES (:key_name, :key_label, :key_value, :description, :is_active, NOW(), NOW())
            ');
            $stmt->execute([
                'key_name' => $apiKey->getKeyName(),
                'key_label' => $apiKey->getKeyLabel(),
                'key_value' => $apiKey->getKeyValue(),
                'description' => $apiKey->getDescription(),
                'is_active' => $apiKey->isActive() ? 1 : 0,
            ]);
        }
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM api_keys WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function mapToEntity(array $data): ApiKey
    {
        return new ApiKey(
            (int) $data['id'],
            $data['key_name'],
            $data['key_label'],
            $data['key_value'],
            $data['description'],
            (bool) $data['is_active'],
            $data['created_at'],
            $data['updated_at']
        );
    }
}
