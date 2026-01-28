<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use JsonSerializable;

class CmsPage implements JsonSerializable
{
    public function __construct(
        private ?int $id,
        private string $slug,
        private string $title,
        private string $template,
        private ?string $blocksJson,
        private ?string $seoTitle,
        private ?string $seoDescription,
        private string $status,
        private ?string $publishedAt,
        private bool $isHomepage = false,
        private string $createdAt = '',
        private string $updatedAt = ''
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function getBlocksJson(): ?string
    {
        return $this->blocksJson;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPublishedAt(): ?string
    {
        return $this->publishedAt;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    public function isHomepage(): bool
    {
        return $this->isHomepage;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'template' => $this->template,
            'blocks_json' => $this->blocksJson,
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'status' => $this->status,
            'published_at' => $this->publishedAt,
            'is_homepage' => $this->isHomepage,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
