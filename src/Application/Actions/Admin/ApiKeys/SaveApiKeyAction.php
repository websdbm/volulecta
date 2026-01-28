<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\ApiKeys;

use App\Domain\Entities\ApiKey;
use App\Domain\Repositories\ApiKeyRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class SaveApiKeyAction
{
    public function __construct(
        private Twig $twig,
        private ApiKeyRepositoryInterface $apiKeyRepository
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = isset($args['id']) ? (int) $args['id'] : null;

        if ($request->getMethod() === 'POST') {
            $data = $request->getParsedBody();

            $apiKey = new ApiKey(
                $id,
                $data['key_name'] ?? '',
                $data['key_label'] ?? '',
                $data['key_value'] ?? '',
                $data['description'] ?? null,
                isset($data['is_active'])
            );

            $this->apiKeyRepository->save($apiKey);

            return $response
                ->withHeader('Location', '/admin/api-keys')
                ->withStatus(302);
        }

        // GET: mostra form
        $apiKey = $id ? $this->apiKeyRepository->findById($id) : null;

        return $this->twig->render($response, 'admin/api-keys/form.twig', [
            'title' => $id ? 'Modifica API Key' : 'Nuova API Key',
            'api_key' => $apiKey
        ]);
    }
}
