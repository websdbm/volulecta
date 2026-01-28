<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\ApiKeys;

use App\Domain\Repositories\ApiKeyRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Twig\Environment;

class ListApiKeysAction
{
    public function __construct(
        private Environment $twig,
        private ApiKeyRepositoryInterface $apiKeyRepository
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $apiKeys = $this->apiKeyRepository->findAll();

        $html = $this->twig->render('admin/api-keys/list.twig', [
            'title' => 'Gestione API Keys',
            'api_keys' => $apiKeys
        ]);

        $response->getBody()->write($html);
        return $response;
    }
}
