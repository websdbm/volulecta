<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Entities\CmsPage;
use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CreatePageAction
{
    public function __construct(
        private Twig $twig,
        private CmsRepositoryInterface $cmsRepository
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();
            
            $page = new CmsPage(
                null,
                $data['slug'],
                $data['title'],
                $data['template'] ?? 'interna',
                null, // blocks_json
                $data['seo_title'] ?? null,
                $data['seo_description'] ?? null,
                $data['status'] ?? 'draft',
                null, // published_at
                '', // created_at
                ''  // updated_at
            );

            $this->cmsRepository->save($page);

            return $response->withHeader('Location', '/admin/cms')->withStatus(302);
        }

        return $this->twig->render($response, 'admin/cms/create.twig', [
            'title' => 'Crea Nuova Pagina',
        ]);
    }
}
