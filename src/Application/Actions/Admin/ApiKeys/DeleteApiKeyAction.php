<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\ApiKeys;

use App\Domain\Repositories\ApiKeyRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DeleteApiKeyAction
{
    public function __construct(
        private ApiKeyRepositoryInterface $apiKeyRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $this->apiKeyRepository->delete($id);

        return $response
            ->withHeader('Location', '/admin/api-keys')
            ->withStatus(302);
    }
}
