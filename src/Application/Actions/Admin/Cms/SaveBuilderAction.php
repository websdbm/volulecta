<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SaveBuilderAction
{
    public function __construct(
        private CmsRepositoryInterface $cmsRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $page = $this->cmsRepository->findById($id);

        if (!$page) {
            return $response->withStatus(404);
        }

        $data = $request->getParsedBody();
        $blocksJson = json_encode($data['blocks'] ?? []);

        // Use a reflection or a setter if available, 
        // but since our Entity is immutable for now, we create a new one
        $updatedPage = new \App\Domain\Entities\CmsPage(
            $page->getId(),
            $page->getSlug(),
            $page->getTitle(),
            $page->getTemplate(),
            $blocksJson,
            $page->getSeoTitle(),
            $page->getSeoDescription(),
            $page->getStatus(),
            $page->getPublishedAt(),
            $page->getCreatedAt(),
            $page->getUpdatedAt()
        );

        $this->cmsRepository->save($updatedPage);

        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
