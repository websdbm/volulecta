<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\ApiKey;

interface ApiKeyRepositoryInterface
{
    public function findAll(): array;
    
    public function findById(int $id): ?ApiKey;
    
    public function findByName(string $keyName): ?ApiKey;
    
    public function save(ApiKey $apiKey): void;
    
    public function delete(int $id): void;
}
