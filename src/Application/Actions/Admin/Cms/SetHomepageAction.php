<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SetHomepageAction
{
    public function __construct(
        private CmsRepositoryInterface $cmsRepository,
        private PDO $db
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $page = $this->cmsRepository->findById($id);

        if (!$page) {
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Deseleziona tutte le altre homepage
        $stmt = $this->db->prepare('UPDATE cms_pages SET is_homepage = 0 WHERE id != :id');
        $stmt->execute(['id' => $id]);

        // Imposta questa pagina come homepage
        $homepagePage = new \App\Domain\Entities\CmsPage(
            $page->getId(),
            $page->getSlug(),
            $page->getTitle(),
            $page->getTemplate(),
            $page->getBlocksJson(),
            $page->getSeoTitle(),
            $page->getSeoDescription(),
            $page->getStatus(),
            $page->getPublishedAt(),
            true, // is_homepage = true
            $page->getCreatedAt(),
            date('Y-m-d H:i:s')
        );

        $this->cmsRepository->save($homepagePage);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Homepage impostata con successo'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
