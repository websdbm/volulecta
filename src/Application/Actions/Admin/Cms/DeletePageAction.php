<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Domain\Repositories\CmsRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeletePageAction
{
    public function __construct(
        private CmsRepositoryInterface $cmsRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->cmsRepository->delete($id);

        return $response->withHeader('Location', '/admin/cms')->withStatus(302);
    }
}
