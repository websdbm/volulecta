<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ListPagesAction
{
    public function __construct(
        private Twig $twig,
        private CmsRepositoryInterface $cmsRepository
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $pages = $this->cmsRepository->findAll();

        return $this->twig->render($response, 'admin/cms/list.twig', [
            'title' => 'Gestione Pagine CMS',
            'pages' => $pages,
        ]);
    }
}
