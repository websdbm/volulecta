<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class PublicPageAction
{
    public function __construct(
        private Twig $twig,
        private CmsRepositoryInterface $cmsRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $slug = $args['slug'];
        $page = $this->cmsRepository->findBySlug($slug);

        if (!$page || ($page->getStatus() !== 'published' && !$this->isAdmin())) {
            return $response->withStatus(404);
        }

        $blocks = json_decode($page->getBlocksJson() ?: '[]', true);
        
        $templateFile = "cms/templates/{$page->getTemplate()}.twig";
        
        return $this->twig->render($response, $templateFile, [
            'title' => $page->getTitle(),
            'page' => $page,
            'blocks' => $blocks,
            'seo' => [
                'title' => $page->getSeoTitle(),
                'description' => $page->getSeoDescription(),
            ]
        ]);
    }

    private function isAdmin(): bool 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return (($_SESSION['user_role'] ?? '') === 'admin');
    }
}
