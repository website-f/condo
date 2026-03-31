<?php

namespace FSPoster\App\SocialNetworks\WordPress\Api\RestApi;

use FSPoster\App\SocialNetworks\WordPress\Api\PostingData;
use FSPoster\GuzzleHttp\Client;

class Api
{
    public AuthData $authData;
    public ?string $proxy = null;

    public string $authException = \Exception::class;
    public string $postException = \Exception::class;

    private ?Client $client = null;

    public function setProxy(?string $proxy): self
    {
        $this->proxy = $proxy;
        return $this;
    }

    public function setAuthData(AuthData $authData): self
    {
        $this->authData = $authData;
        return $this;
    }

    public function getClient(): Client
    {
        if (is_null($this->client)) {
            $this->client = new Client([
                'proxy'       => $this->proxy ?: null,
                'verify'      => false,
                'http_errors' => false,
                'auth'        => [
                    $this->authData->applicationName,
                    $this->authData->applicationPassword
                ],
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'User-Agent'   => 'FSPoster-WPClient'
                ],
            ]);
        }
        return $this->client;
    }

    public function apiRequest(string $endpoint, string $method = 'GET', array $data = [])
    {
        $url = rtrim($this->authData->siteUrl, '/') . '/wp-json/' . ltrim($endpoint, '/');
        $client = $this->getClient();

        $options = [];
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $data;
        }

        $response = $client->request($method, $url, $options);
        return json_decode($response->getBody(), true);
    }

    public function getMyInfo(): bool
    {
        $response = $this->apiRequest('wp/v2/users/me', 'GET');
        if (isset($response['id'])) {
            return true;
        }
        throw new $this->authException('Authentication failed: ' . json_encode($response));
    }

    public function sendPost(PostingData $postingData): string
    {
        $data = [
            'title'   => $postingData->title,
            'content' => $postingData->message,
            'excerpt' => $postingData->excerpt ?? '',
            'status'  => $postingData->postStatus ?? 'publish',
        ];

        if (!empty($postingData->categories)) {
            $data['categories'] = array_column($postingData->categories, 'id');
        }

        if (!empty($postingData->tags)) {
            $data['tags'] = array_column($postingData->tags, 'id');
        }

        if (!empty($postingData->uploadMedia)) {
            $uploadMedia = reset($postingData->uploadMedia);
            $mediaId = $this->uploadMedia($uploadMedia['path']);
            if ($mediaId) {
                $data['featured_media'] = $mediaId;
            }
        }

        $response = $this->apiRequest('wp/v2/posts', 'POST', $data);

        if (!isset($response['id'])) {
            throw new $this->postException('Failed to create post: ' . json_encode($response));
        }

        return (string)$response['id'];
    }

    public function uploadMedia(string $filePath)
    {
        $client = $this->getClient();
        $url = rtrim($this->authData->siteUrl, '/') . '/wp-json/wp/v2/media';

        $response = $client->post($url, [
            'headers' => [
                'Content-Disposition' => 'attachment; filename="' . basename($filePath) . '"',
            ],
            'body' => file_get_contents($filePath),
        ]);

        $result = json_decode($response->getBody(), true);
        return $result['id'] ?? false;
    }

    public function setAuthException ( string $exceptionClass ): self
    {
        $this->authException = $exceptionClass;

        return $this;
    }

    public function setPostException ( string $exceptionClass ): self
    {
        $this->postException = $exceptionClass;

        return $this;
    }
}
