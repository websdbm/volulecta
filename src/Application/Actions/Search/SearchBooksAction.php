<?php

declare(strict_types=1);

namespace App\Application\Actions\Search;

use App\Application\Services\BookSearchService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class SearchBooksAction
{
    public function __construct(
        private BookSearchService $bookSearchService
    ) {
    }

    public function __invoke(Request $request, Response $response): Response
    {
        // Se è una richiesta GET, mostra la pagina
        if ($request->getMethod() === 'GET') {
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }

        // Se è POST, elabora la ricerca
        $query = $request->getParsedBody()['query'] ?? '';
        $language = $request->getParsedBody()['language'] ?? null;
        
        // Valida la query
        if (!$this->bookSearchService->validateQuery($query)) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Query non valida. Inserisci almeno 2 caratteri.',
                'results' => []
            ], 400);
        }

        // Sanitizza la query
        $sanitized = $this->bookSearchService->sanitizeQuery($query);

        try {
            // Ricerca i libri con la lingua specificata
            $results = $this->bookSearchService->searchAmazon($sanitized, 20, $language);

            return $this->jsonResponse($response, [
                'success' => true,
                'query' => $sanitized,
                'source' => $this->bookSearchService->getLastSource(),
                'count' => count($results),
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, [
                'success' => false,
                'error' => 'Errore durante la ricerca: ' . $e->getMessage(),
                'source' => $this->bookSearchService->getLastSource(),
                'results' => []
            ], 500);
        }
    }

    /**
     * Ritorna una risposta JSON
     */
    private function jsonResponse(Response $response, array $data, int $status = 200): Response
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $stream = new Stream(fopen('php://memory', 'r+'));
        $stream->write($json);
        $stream->rewind();

        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);
    }
}
