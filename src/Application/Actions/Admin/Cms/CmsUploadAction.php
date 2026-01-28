<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin\Cms;

use App\Application\Services\UploadService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CmsUploadAction
{
    public function __construct(
        private UploadService $uploadService
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (empty($uploadedFiles['file'])) {
            return $response->withStatus(400);
        }

        $file = $uploadedFiles['file'];
        $path = $this->uploadService->upload($file, 'cms');

        $response->getBody()->write(json_encode(['path' => $path]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
