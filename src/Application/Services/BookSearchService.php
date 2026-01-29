<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Repositories\ApiKeyRepositoryInterface;
use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Amazon\ProductAdvertisingAPI\v1\Configuration;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\api\DefaultApi;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\Item;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\PartnerType;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsRequest;
use Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\SearchItemsResource;
use GuzzleHttp\Client as GuzzleClient;

/**
 * Servizio per la ricerca di libri da fonti esterne
 * Utilizza Amazon Product Advertising API 5.0 se disponibile
 * Fallback a Open Library API (gratuita) se Amazon non è configurato
 */
class BookSearchService
{
    private const OPEN_LIBRARY_API = 'https://openlibrary.org/search.json';

    private const PAAPI_HOSTS = [
        'it' => [
            'host' => 'webservices.amazon.it',
            'region' => 'eu-west-1',
            'marketplace' => 'www.amazon.it',
            'partner_tag_env' => 'AMAZON_ASSOCIATE_TAG_IT',
        ],
        'en' => [
            'host' => 'webservices.amazon.com',
            'region' => 'us-east-1',
            'marketplace' => 'www.amazon.com',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'es' => [
            'host' => 'webservices.amazon.es',
            'region' => 'eu-west-1',
            'marketplace' => 'www.amazon.es',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'fr' => [
            'host' => 'webservices.amazon.fr',
            'region' => 'eu-west-1',
            'marketplace' => 'www.amazon.fr',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'de' => [
            'host' => 'webservices.amazon.de',
            'region' => 'eu-west-1',
            'marketplace' => 'www.amazon.de',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'pt' => [
            'host' => 'webservices.amazon.com.br',
            'region' => 'us-east-1',
            'marketplace' => 'www.amazon.com.br',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'ja' => [
            'host' => 'webservices.amazon.co.jp',
            'region' => 'us-west-2',
            'marketplace' => 'www.amazon.co.jp',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
        'zh' => [
            'host' => 'webservices.amazon.cn',
            'region' => 'cn-north-1',
            'marketplace' => 'www.amazon.cn',
            'partner_tag_env' => 'AMAZON_PARTNER_TAG',
        ],
    ];
    
    public function __construct(
        private ApiKeyRepositoryInterface $apiKeyRepository
    ) {
    }

    private string $lastSource = 'open_library';

    public function getLastSource(): string
    {
        return $this->lastSource;
    }

    /**
     * Ricerca libri da Amazon utilizzando le credenziali configurate
     * 
     * @param string $query Termine di ricerca
     * @param int $limit Numero di risultati (default 10)
     * @param string|null $language Codice lingua (es: 'it', 'en', 'es')
     * @return array Array di libri trovati
     */
    public function searchAmazon(string $query, int $limit = 10, ?string $language = null): array
    {
        // Prima verifica se le credenziali Amazon sono disponibili
        $amazonKey = $this->apiKeyRepository->findByName('amazon');
        
        if ($amazonKey && $amazonKey->isActive()) {
            try {
                $results = $this->searchAmazonAPI($amazonKey, $query, $limit, $language);
                if (!empty($results)) {
                    return $results;
                }
            } catch (\Exception $e) {
                error_log("Amazon API error: " . $e->getMessage());
            }
        }
        
        // Fallback: usa Open Library come fonte alternativa
        return $this->searchOpenLibrary($query, $limit, $language);
    }
    
    /**
     * Ricerca via Amazon Product Advertising API 5.0
     */
    private function searchAmazonAPI(object $amazonKey, string $query, int $limit = 10, ?string $language = null): array
    {
        $accessKeyId = $amazonKey->getKeyValue();
        $secretAccessKey = $amazonKey->getKeyValueSecondary();

        $configData = $this->getPaapiConfig($language);
        $partnerTag = $this->getPartnerTag($language);

        if (!$accessKeyId || !$secretAccessKey || !$partnerTag) {
            throw new \RuntimeException('Amazon PAAPI: credenziali o partner tag mancanti.');
        }

        $config = new Configuration();
        $config->setAccessKey($accessKeyId);
        $config->setSecretKey($secretAccessKey);
        $config->setHost($configData['host']);
        $config->setRegion($configData['region']);

        $apiInstance = new DefaultApi(new GuzzleClient(), $config);

        $itemCount = max(1, min($limit, 10));

        $resources = [
            SearchItemsResource::ITEM_INFOTITLE,
            SearchItemsResource::ITEM_INFOBY_LINE_INFO,
            SearchItemsResource::ITEM_INFOCONTENT_INFO,
            SearchItemsResource::ITEM_INFOEXTERNAL_IDS,
            SearchItemsResource::IMAGESPRIMARYMEDIUM,
            SearchItemsResource::IMAGESPRIMARYLARGE,
        ];

        $request = new SearchItemsRequest();
        $request->setSearchIndex('Books');
        $request->setKeywords($query);
        $request->setItemCount($itemCount);
        $request->setPartnerTag($partnerTag);
        $request->setPartnerType(PartnerType::ASSOCIATES);
        $request->setResources($resources);
        $request->setMarketplace($configData['marketplace']);

        $invalid = $request->listInvalidProperties();
        if (!empty($invalid)) {
            error_log('Amazon PAAPI: richiesta non valida - ' . implode('; ', $invalid));
            return [];
        }

        try {
            $response = $apiInstance->searchItems($request);
        } catch (ApiException $e) {
            $message = $this->extractPaapiErrorMessage($e->getResponseBody());
            throw new \RuntimeException($message ?: $e->getMessage());
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage());
        }

        $errors = $response->getErrors();
        if (!empty($errors)) {
            $firstError = $errors[0] ?? null;
            $message = $firstError && method_exists($firstError, 'getMessage') ? $firstError->getMessage() : 'Errore PAAPI.';
            throw new \RuntimeException($message);
        }

        $items = $response->getSearchResult()?->getItems() ?? [];
        if (empty($items)) {
            return [];
        }

        $results = [];
        foreach ($items as $item) {
            if ($item instanceof Item) {
                $results[] = $this->normalizeAmazonItem($item, $language);
            }
        }

        $results = array_values(array_filter($results, fn($result) => !empty($result['title']) && $result['title'] !== 'N/A'));
        if (!empty($results)) {
            $this->lastSource = 'amazon';
        }

        return $results;
    }

    /**
     * Normalizza i risultati Amazon in formato standard
     */
    private function normalizeAmazonItem(Item $item, ?string $language = null): array
    {
        $asin = $item->getASIN() ?? '';
        $detailUrl = $item->getDetailPageURL();

        $title = 'N/A';
        $author = '';
        $publisher = '';
        $year = null;
        $isbn = null;
        $coverUrl = null;

        $itemInfo = $item->getItemInfo();
        if ($itemInfo) {
            $titleAttr = $itemInfo->getTitle();
            if ($titleAttr && $titleAttr->getDisplayValue()) {
                $title = $titleAttr->getDisplayValue();
            }

            $byLine = $itemInfo->getByLineInfo();
            if ($byLine) {
                $contributors = $byLine->getContributors();
                if (is_array($contributors) && !empty($contributors)) {
                    $names = [];
                    foreach ($contributors as $contributor) {
                        $name = $contributor->getName();
                        if ($name) {
                            $names[] = $name;
                        }
                    }
                    $author = implode(', ', $names);
                }

                $manufacturer = $byLine->getManufacturer();
                if ($manufacturer && $manufacturer->getDisplayValue()) {
                    $publisher = $manufacturer->getDisplayValue();
                }
            }

            $contentInfo = $itemInfo->getContentInfo();
            if ($contentInfo) {
                $publicationDate = $contentInfo->getPublicationDate();
                if ($publicationDate && $publicationDate->getDisplayValue()) {
                    $yearValue = substr($publicationDate->getDisplayValue(), 0, 4);
                    $year = is_numeric($yearValue) ? (int)$yearValue : null;
                }
            }

            $externalIds = $itemInfo->getExternalIds();
            if ($externalIds) {
                $isbns = $externalIds->getISBNs();
                if ($isbns && $isbns->getDisplayValues()) {
                    $values = $isbns->getDisplayValues();
                    $isbn = $values[0] ?? null;
                } else {
                    $eans = $externalIds->getEANs();
                    if ($eans && $eans->getDisplayValues()) {
                        $values = $eans->getDisplayValues();
                        $isbn = $values[0] ?? null;
                    }
                }
            }
        }

        $images = $item->getImages();
        if ($images && $images->getPrimary()) {
            $primary = $images->getPrimary();
            $large = $primary->getLarge();
            $medium = $primary->getMedium();
            $small = $primary->getSmall();

            if ($large && $large->getURL()) {
                $coverUrl = $large->getURL();
            } elseif ($medium && $medium->getURL()) {
                $coverUrl = $medium->getURL();
            } elseif ($small && $small->getURL()) {
                $coverUrl = $small->getURL();
            }
        }

        $amazonLink = $detailUrl ?: "https://{$this->getAmazonDomain($language)}/dp/{$asin}";

        return [
            'id' => $asin,
            'title' => $title,
            'author' => $author,
            'publisher' => $publisher,
            'year' => $year,
            'isbn' => $isbn,
            'cover_url' => $coverUrl,
            'source' => 'amazon',
            'amazon_link' => $amazonLink,
        ];
    }

    /**
     * Configura host/region/marketplace e partner tag in base alla lingua
     */
    private function getPaapiConfig(?string $language): array
    {
        $lang = $language ?? 'en';
        $config = self::PAAPI_HOSTS[$lang] ?? self::PAAPI_HOSTS['en'];

        return [
            'host' => $config['host'],
            'region' => $config['region'],
            'marketplace' => $config['marketplace'],
        ];
    }

    /**
     * Recupera il partner tag da env o da api_keys
     */
    private function getPartnerTag(?string $language): string
    {
        if ($language === 'it') {
            $tag = $this->getEnvValue('AMAZON_ASSOCIATE_TAG_IT');
            if (!empty($tag)) {
                return $tag;
            }
        }

        $tag = $this->getEnvValue('AMAZON_PARTNER_TAG');
        if (!empty($tag)) {
            return $tag;
        }

        $candidateKeys = ['amazon_partner_tag', 'amazon_affiliate_tag', 'amazon_tag', 'amazon_partner'];
        foreach ($candidateKeys as $keyName) {
            $key = $this->apiKeyRepository->findByName($keyName);
            if ($key && $key->isActive() && $key->getKeyValue()) {
                return trim($key->getKeyValue());
            }
        }

        return '';
    }

    /**
     * Legge una variabile d'ambiente da getenv/$_ENV
     */
    private function getEnvValue(string $key): string
    {
        $value = getenv($key);
        if ($value !== false && $value !== null) {
            return (string)$value;
        }

        return (string)($_ENV[$key] ?? '');
    }

    private function extractPaapiErrorMessage(?string $responseBody): string
    {
        if (!$responseBody) {
            return '';
        }

        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded)) {
            return '';
        }

        $errors = $decoded['Errors'] ?? [];
        if (!is_array($errors) || empty($errors)) {
            return '';
        }

        $first = $errors[0] ?? [];
        $message = $first['Message'] ?? '';
        $code = $first['Code'] ?? '';

        if ($code && $message) {
            return $code . ': ' . $message;
        }

        return $message ?: '';
    }
    
    /**
     * Ottiene il dominio Amazon per la lingua
     */
    private function getAmazonDomain(?string $language): string
    {
        $domainMap = [
            'it' => 'amazon.it',
            'en' => 'amazon.com',
            'es' => 'amazon.es',
            'fr' => 'amazon.fr',
            'de' => 'amazon.de',
            'pt' => 'amazon.com.br',
            'ru' => 'amazon.ru',
            'zh' => 'amazon.cn',
            'ja' => 'amazon.co.jp',
            'ko' => 'amazon.co.kr',
        ];
        
        return $domainMap[$language] ?? 'amazon.com';
    }

    /**
     * Ricerca libri da Open Library API (gratuita)
     * 
     * @param string $query Termine di ricerca
     * @param int $limit Numero di risultati
     * @param string|null $language Codice lingua (es: 'it', 'en', 'es')
     * @return array Array di libri con informazioni standardizzate
     */
    private function searchOpenLibrary(string $query, int $limit = 10, ?string $language = null): array
    {
        try {
            $this->lastSource = 'open_library';
            // Mappa i codici lingua a quelli supportati da Open Library
            $languageMap = [
                'it' => 'ita',    // Italiano
                'en' => 'eng',    // Inglese
                'es' => 'spa',    // Spagnolo
                'fr' => 'fra',    // Francese
                'de' => 'deu',    // Tedesco
                'pt' => 'por',    // Portoghese
                'ru' => 'rus',    // Russo
                'zh' => 'zho',    // Cinese
                'ja' => 'jpn',    // Giapponese
                'ko' => 'kor',    // Coreano
            ];
            
            // Primo tentativo: ricerca con filtro lingua se specificato
            if ($language && isset($languageMap[$language])) {
                $results = $this->queryOpenLibrary($query, $limit, $languageMap[$language], $language);
                if (!empty($results)) {
                    return $results;
                }
            }
            
            // Fallback: ricerca senza filtro lingua se il primo tentativo è vuoto
            return $this->queryOpenLibrary($query, $limit, null, $language);
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Esegue una query a Open Library
     */
    private function queryOpenLibrary(string $query, int $limit, ?string $languageCode = null, ?string $language = null): array
    {
        $params = [
            'q' => $query,
            'limit' => $limit,
            'fields' => 'key,title,author_name,first_publish_year,isbn,cover_i,publisher,language'
        ];
        
        // Aggiungi il filtro per lingua se specificato
        if ($languageCode) {
            $params['language'] = $languageCode;
        }
        
        $url = self::OPEN_LIBRARY_API . '?' . http_build_query($params);

        $response = @file_get_contents($url);
        if ($response === false) {
            return [];
        }

        $data = json_decode($response, true);
        if (!isset($data['docs'])) {
            return [];
        }

        return array_map(fn($doc) => $this->normalizeOpenLibraryBook($doc, $language), $data['docs']);
    }

    /**
     * Normalizza i dati da Open Library al formato standard
     */
    private function normalizeOpenLibraryBook(array $data, ?string $language = null): array
    {
        $coverUrl = isset($data['cover_i']) 
            ? "https://covers.openlibrary.org/b/id/{$data['cover_i']}-M.jpg"
            : null;

        // Mappa lingue ad Amazon locale
        $amazonDomains = [
            'it' => 'amazon.it',
            'en' => 'amazon.com',
            'es' => 'amazon.es',
            'fr' => 'amazon.fr',
            'de' => 'amazon.de',
            'pt' => 'amazon.com.br',
            'ru' => 'amazon.ru',
            'zh' => 'amazon.cn',
            'ja' => 'amazon.co.jp',
            'ko' => 'amazon.co.kr',
        ];
        
        $domain = $amazonDomains[$language] ?? 'amazon.com';
        $protocol = strpos($domain, 'amazon.') === 0 ? 'https://' : 'https://';
        
        // Crea link Amazon locale
        $amazonLink = isset($data['isbn'][0]) 
            ? $protocol . $domain . "/s?k=" . urlencode($data['isbn'][0])
            : $protocol . $domain . "/s?k=" . urlencode($data['title'] ?? '');

        return [
            'id' => $data['key'] ?? '',
            'title' => $data['title'] ?? 'N/A',
            'author' => implode(', ', $data['author_name'] ?? []),
            'publisher' => implode(', ', $data['publisher'] ?? []),
            'year' => $data['first_publish_year'] ?? null,
            'isbn' => $data['isbn'][0] ?? null,
            'cover_url' => $coverUrl,
            'source' => 'open_library',
            'amazon_link' => $amazonLink
        ];
    }

    /**
     * Ricerca libri da Google Books API
     * Nota: Richiede API key di Google
     * 
     * @param string $query Termine di ricerca
     * @param int $limit Numero di risultati
     * @return array Array di libri trovati
     */
    public function searchGoogleBooks(string $query, int $limit = 10): array
    {
        // Potrebbe essere implementato con una Google API key configurabile
        // Per ora ritorna array vuoto
        return [];
    }

    /**
     * Valida una query di ricerca
     * 
     * @param string $query Termine di ricerca
     * @return bool True se valido, false altrimenti
     */
    public function validateQuery(string $query): bool
    {
        $query = trim($query);
        
        // Minimo 2 caratteri
        if (strlen($query) < 2) {
            return false;
        }
        
        // Massimo 200 caratteri
        if (strlen($query) > 200) {
            return false;
        }
        
        // Non deve contenere caratteri pericolosi
        if (preg_match('/[<>\"\'%;()&+]/', $query)) {
            return false;
        }
        
        return true;
    }

    /**
     * Sanitizza la query di ricerca
     */
    public function sanitizeQuery(string $query): string
    {
        return trim(htmlspecialchars($query, ENT_QUOTES, 'UTF-8'));
    }
}
