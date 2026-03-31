<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Exception\GuzzleException;

/**
 * Cloudflare Worker client for Twitter API requests
 */
class CloudflareWorker
{
    private const WORKER_URL = 'https://twitter-proxy.fs-poster.com';

    private WorkerCredentialsDTO $credentials;
    private ?string $proxy = null;

    public function __construct(WorkerCredentialsDTO $credentials)
    {
        $this->credentials = $credentials;
    }

    public function setProxy(?string $proxy): self
    {
        $this->proxy = $proxy;
        return $this;
    }

    /**
     * Send request via Cloudflare Worker
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function sendRequest(string $url, string $method, array $headers, string $body): array
    {
        $client = new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 30,
            'proxy' => $this->proxy ?: null,
        ]);

        $workerPayload = [
            'url' => $url,
            'method' => $method,
            'headers' => $headers,
            'body' => $body,
        ];

        $response = $client->post(self::WORKER_URL, [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-FSP-License' => $this->credentials->licenseCode,
                'X-FSP-Domain' => $this->credentials->domain,
            ],
            'body' => json_encode($workerPayload, JSON_THROW_ON_ERROR),
        ]);

        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        return [
            'status' => $result['status'] ?? 500,
            'body' => $result['body'] ?? '',
        ];
    }
}
