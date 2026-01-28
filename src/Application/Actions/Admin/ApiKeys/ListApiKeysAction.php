<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\ApiKeys;

use App\Domain\Repositories\ApiKeyRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ListApiKeysAction
{
    public function __construct(
        private Twig $twig,
        private ApiKeyRepositoryInterface $apiKeyRepository
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $apiKeys = $this->apiKeyRepository->findAll();

        return $this->twig->render($response, 'admin/api-keys/list.twig', [
            'title' => 'Gestione API Keys',
            'api_keys' => $apiKeys
        ]);
    }
}
