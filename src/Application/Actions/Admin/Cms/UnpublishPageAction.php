<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UnpublishPageAction
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
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Non permettere di mettere in bozza una homepage (come WordPress)
        if ($page->isHomepage()) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Non è possibile mettere in bozza la homepage. Imposta prima un\'altra pagina come homepage.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Crea una nuova istanza con status = 'draft'
        $draftPage = new \App\Domain\Entities\CmsPage(
            $page->getId(),
            $page->getSlug(),
            $page->getTitle(),
            $page->getTemplate(),
            $page->getBlocksJson(),
            $page->getSeoTitle(),
            $page->getSeoDescription(),
            'draft', // cambio status a draft
            $page->getPublishedAt(),
            $page->isHomepage(),
            $page->getCreatedAt(),
            date('Y-m-d H:i:s') // updated_at = NOW()
        );

        $this->cmsRepository->save($draftPage);

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'message' => 'Pagina impostata come bozza'
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
