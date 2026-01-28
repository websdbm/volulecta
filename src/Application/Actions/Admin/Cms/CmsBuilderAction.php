<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class CmsBuilderAction
{
    public function __construct(
        private Twig $twig,
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

        return $this->twig->render($response, 'admin/cms/builder.twig', [
            'title' => 'Builder - ' . $page->getTitle(),
            'page' => $page,
        ]);
    }
}
