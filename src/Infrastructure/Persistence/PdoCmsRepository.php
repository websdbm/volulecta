<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entities\CmsPage;
use App\Domain\Repositories\CmsRepositoryInterface;
use PDO;

class PdoCmsRepository implements CmsRepositoryInterface
{
    public function __construct(private PDO $db)
    {
    }

    public function findById(int $id): ?CmsPage
    {
        $stmt = $this->db->prepare('SELECT * FROM cms_pages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findBySlug(string $slug): ?CmsPage
    {
        $stmt = $this->db->prepare('SELECT * FROM cms_pages WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);
        $data = $stmt->fetch();

        return $data ? $this->mapToEntity($data) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->db->query('SELECT * FROM cms_pages ORDER BY created_at DESC');
        $pages = [];
        while ($data = $stmt->fetch()) {
            $pages[] = $this->mapToEntity($data);
        }
        return $pages;
    }

    public function save(CmsPage $page): CmsPage
    {
        if ($page->getId() === null) {
            $stmt = $this->db->prepare('
            INSERT INTO cms_pages (slug, title, template, blocks_json, seo_title, seo_description, status, published_at, is_homepage, created_at, updated_at)
            VALUES (:slug, :title, :template, :blocks_json, :seo_title, :seo_description, :status, :published_at, :is_homepage, NOW(), NOW())
        ');
            $stmt->execute([
                'slug' => $page->getSlug(),
                'title' => $page->getTitle(),
                'template' => $page->getTemplate(),
                'blocks_json' => $page->getBlocksJson(),
                'seo_title' => $page->getSeoTitle(),
                'seo_description' => $page->getSeoDescription(),
                'status' => $page->getStatus(),
                'published_at' => $page->getPublishedAt(),
                'is_homepage' => $page->isHomepage() ? 1 : 0,
            ]);
            $id = (int) $this->db->lastInsertId();
            return $this->findById($id);
        }

        $stmt = $this->db->prepare('
            UPDATE cms_pages
            SET slug = :slug, title = :title, template = :template, blocks_json = :blocks_json, 
                seo_title = :seo_title, seo_description = :seo_description, status = :status, 
                published_at = :published_at, is_homepage = :is_homepage, updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            'id' => $page->getId(),
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
            'template' => $page->getTemplate(),
            'blocks_json' => $page->getBlocksJson(),
            'seo_title' => $page->getSeoTitle(),
            'seo_description' => $page->getSeoDescription(),
            'status' => $page->getStatus(),
            'published_at' => $page->getPublishedAt(),
            'is_homepage' => $page->isHomepage() ? 1 : 0,
        ]);
        return $this->findById($page->getId());
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM cms_pages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    private function mapToEntity(array $data): CmsPage
    {
        return new CmsPage(
            (int) $data['id'],
            $data['slug'],
            $data['title'],
            $data['template'],
            $data['blocks_json'],
            $data['seo_title'],
            $data['seo_description'],
            $data['status'],
            $data['published_at'],
            (bool) $data['is_homepage'],
            $data['created_at'],
            $data['updated_at']
        );
    }
}
