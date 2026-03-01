<?php

declare(strict_types=1);

namespace Shanginn\YandexWordstat;

use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Shanginn\YandexWordstat\Exceptions\WordstatApiErrorException;
use Shanginn\YandexWordstat\Exceptions\WordstatException;
use Shanginn\YandexWordstat\Exceptions\WordstatRateLimitException;

final readonly class WordstatClient implements WordstatClientInterface
{
    private HttpClient $client;
    private string $baseUrl;

    public function __construct(
        private string $oauthToken,
    ) {
        $this->client = HttpClientBuilder::buildDefault();
        $this->baseUrl = "https://api.wordstat.yandex.net/v1";
    }

    public function post(string $endpoint, string $jsonBody = ''): string
    {
        $url = "{$this->baseUrl}/{$endpoint}";

        $request = new Request($url, 'POST');
        if ($jsonBody !== '') {
            $request->setBody($jsonBody);
        }
        $request->setHeader('Authorization', "Bearer {$this->oauthToken}");
        $request->setHeader('Content-Type', 'application/json;charset=utf-8');
        $request->setTransferTimeout(30);
        $request->setInactivityTimeout(30);

        try {
            $response = $this->client->request($request);
            $body = $response->getBody()->buffer();
            $status = $response->getStatus();

            if ($status >= 400) {
                $data = json_decode($body, true);
                $msg = $data['error_description'] ?? $data['error']['message'] ?? "HTTP $status error";

                if ($status === 429) {
                    throw new WordstatRateLimitException($msg);
                }

                throw new WordstatApiErrorException($msg, $status);
            }

            return $body;

        } catch (\Throwable $e) {
            if ($e instanceof WordstatApiErrorException) {
                throw $e;
            }
            throw new WordstatException("Request failed: " . $e->getMessage(), 0, $e);
        }
    }
}
