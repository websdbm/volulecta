<?php

declare(strict_types=1);

namespace App\Application\Actions\Search;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class BookSearchPageAction
{
    public function __construct(
        private Twig $twig
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'search/books.twig', [
            'title' => 'Ricerca Libri',
            'page_type' => $request->getAttribute('page_type') ?? 'admin'
        ]);
    }
}
