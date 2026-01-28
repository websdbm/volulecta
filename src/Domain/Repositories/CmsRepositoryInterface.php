<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Entities\CmsPage;

interface CmsRepositoryInterface
{
    public function findById(int $id): ?CmsPage;
    public function findBySlug(string $slug): ?CmsPage;
    public function findAll(): array;
    public function save(CmsPage $page): CmsPage;
    public function delete(int $id): void;
}
