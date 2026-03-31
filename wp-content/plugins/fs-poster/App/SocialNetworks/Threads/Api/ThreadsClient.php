<?php

namespace FSPoster\App\SocialNetworks\Threads\Api;

use Exception;
use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Exception\GuzzleException;
use FSPoster\Psr\Http\Message\ResponseInterface;
use JsonException;

class ThreadsClient
{
    private Client $httpClient;
    public ThreadsClientAuthData $authData;
    public ?string  $proxy = null;
    public string $authException = Exception::class;
    public string $postException = Exception::class;

    public function __construct($options)
    {
        $this->proxy = empty($options['proxy']) ? null : $options['proxy'];
        $this->httpClient = new Client([
            'verify'        => false,
            'proxy'         => $this->proxy,
            'http_errors'   => false
        ]);
        $this->authData = new ThreadsClientAuthData();
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    private function requestWithRetry(string $method, string $url, array $options = [], int $maxAttempts = 5): ResponseInterface
    {
        $noRetry = [
            190,                                    // invalid/expired token
            ...range(100, 115),                     // bad params
            ...range(200, 299),                     // permission errors
            ...range(400, 403),                     // auth errors
            ...range(450, 468),                     // session errors
        ];

        $attempt = 0;

        while (true) {
            try {
                $res  = $this->requestWithAuth($method, $url, $options);
                $body = json_decode($res->getBody()->getContents(), true);

                if (isset($body['error'])) {
                    $code = $body['error']['code'] ?? 0;
                    throw new Exception($body['error']['message'] ?? 'Threads API error', $code);
                }

                $res->getBody()->rewind();

                return $res;
            } catch (GuzzleException $e) {
                if (++$attempt >= $maxAttempts) {
                    throw $e;
                }

                sleep(2 ** $attempt);
            } catch (Exception $e) {
                $code = $e->getCode();

                if (in_array($code, $noRetry, true) || ++$attempt >= $maxAttempts) {
                    throw $e;
                }

                $isRateLimit = in_array($code, [4, 17, 341, 613], true);
                sleep($isRateLimit ? 60 : 2 ** $attempt);
            }
        }
    }

    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function sendPost(PostingData $postingData): array
    {
        $textContent = $postingData->message;
        $containers = [];

        foreach ($postingData->uploadMedia as $media) {
            $data = [];

            if ($media['type'] === 'image') {
                $data['media_type'] = 'IMAGE';
                $data['image_url'] = $media['url'];
            } elseif ($media['type'] === 'video') {
                $data['media_type'] = 'VIDEO';
                $data['video_url'] = $media['url'];
            }

            if (count($postingData->uploadMedia) > 1) {
                $data['is_carousel_item'] = true;
            } else {
                $data['text'] = $textContent;
            }

            $containers[] = [
                'id' => $this->createMediaContainer($data),
                'content' => $data
            ];
        }

        foreach ($containers as $container)
        {
            $this->checkMediaContainerIsFinished($container);
        }

        if (empty($containers)) {
            $content = [
                'media_type' => 'TEXT',
                'text' => $textContent
            ];

            if (!empty($postingData->link)) {
                $content['link_attachment'] = $postingData->link;
            }

            $containers[] = [
                'id' => $this->createMediaContainer($content),
                'content' => $content
            ];
        }

        if (count($containers) > 1) {
            $parentContainerId = $this->createMediaContainer([
                'media_type' => 'CAROUSEL',
                'children' => implode(',', array_column($containers, 'id')),
                'text' => $textContent
            ]);
        } else {
            $parentContainerId = $containers[0]['id'];
        }

        $postId = $this->publishMediaContainer($parentContainerId);

        return $this->getThreadsById($postId);
    }

    public function exchangeCodeForShortLivedAccessToken($code, $redirectUri)
    {
        $res = $this->httpClient->post('https://graph.threads.net/oauth/access_token', [
            'json' => [
                'client_id' => $this->authData->clientId,
                'client_secret' => $this->authData->clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code
            ]
        ]);

        $body = json_decode((string)$res->getBody(), true);

        if (isset($body['access_token'], $body['user_id'])) {
            $this->authData->userId = $body['user_id'];
            $this->authData->userAccessToken = $body['access_token'];
            $this->authData->userAccessTokenExpiresAt = time() + 3600; // 1 hour
        }
    }

    public function exchangeShortLivedForLongLivedAccessToken()
    {
        $res = $this->httpClient->get(
            "https://graph.threads.net/access_token?grant_type=th_exchange_token&client_secret=" .
            $this->authData->clientSecret . "&access_token=" . $this->authData->userAccessToken
        );

        $body = json_decode((string)$res->getBody(), true);

        if (isset($body['access_token'], $body['expires_in'])) {
            $this->authData->userAccessToken = $body['access_token'];
            $this->authData->userAccessTokenExpiresAt = time() + $body['expires_in'];
        }
    }

    /**
     * @throws JsonException
     */
    public function getMe()
    {
        $res = $this->requestWithAuth('GET', "https://graph.threads.net/v1.0/me?fields=id,username,name,threads_profile_picture_url,threads_biography");
        return json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    public function prepare(): void
    {
        if ( $this->authData->userAccessTokenExpiresAt - time() < 86400 * 7 ) // 7 day in seconds
        {
            $this->refreshAccessToken();
        }
    }

    /**
     * @throws JsonException|GuzzleException
     */
    public function refreshAccessToken(): void
    {
        $res = $this->requestWithAuth('GET', "https://graph.threads.net/refresh_access_token?grant_type=th_refresh_token&access_token=" . $this->authData->userAccessToken);

        $body = json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($body['access_token'], $body['expires_in']))
        {
            $this->authData->userAccessToken = $body['access_token'];
            $this->authData->userAccessTokenExpiresAt = time() + $body['expires_in'];
        }
    }

    /**
     * Create media container and return its ID.
     *
     * @throws Exception|GuzzleException
     */
    public function createMediaContainer($content): string
    {
        $res = $this->requestWithRetry('POST', "https://graph.threads.net/v1.0/{$this->authData->userId}/threads", [
            'json' => $content
        ]);

        $body = $this->decodeBodyFromResponse($res);

        if (isset($body['id'])) {
            return $body['id'];
        }

        throw new Exception(fsp__('Unknown error occurred while creating media container for Threads'));
    }

    /**
     * @throws JsonException|GuzzleException
     */
    public function getMediaContainer(string $containerId)
    {
        $res = $this->requestWithRetry(
            'GET',
            "https://graph.threads.net/v1.0/{$containerId}?fields=status,error_message"
        );

        return json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function checkMediaContainerIsFinished(array $container): void
    {
        $status = null;
        $maxAttempts = 15;
        $sleepSeconds = 20;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $containerInf = $this->getMediaContainer($container['id']);
            $status = isset($containerInf['status']) ? strtoupper($containerInf['status']) : null;

            if ($status === 'IN_PROGRESS') {
                sleep($sleepSeconds);
                continue;
            }
            break;
        }

        if ($status === 'FINISHED') {
            return;
        }

        if ($status === 'ERROR' || $status === 'EXPIRED') {
            $errorMessage = $containerInf['error_message'] ?? 'Unknown error';
            throw new $this->postException(fsp__('Threads container failed') . ': ' . $errorMessage);
        }

        if ($status === 'IN_PROGRESS') {
            throw new $this->postException(fsp__('Threads container still in progress after %d seconds', [$maxAttempts * $sleepSeconds]));
        }

        throw new $this->postException(fsp__('Unknown error occurred while checking status of Threads video media container.'));
    }

    /**
     * @throws Exception|GuzzleException
     */
    public function publishMediaContainer(string $containerId): string
    {
        $res = $this->requestWithRetry('POST', "https://graph.threads.net/v1.0/{$this->authData->userId}/threads_publish?creation_id=" . $containerId);

        $body = $this->decodeBodyFromResponse($res);

        return $body['id'];
    }

    /**
     * @throws JsonException|GuzzleException
     */
    public function getThreadsById(string $threadsId)
    {
        $res = $this->requestWithAuth('GET', "https://graph.threads.net/v1.0/{$threadsId}?fields=id,shortcode,permalink");
        return json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GuzzleException
     */
    private function requestWithAuth(string $method, $uri = '', array $options = [])
    {
        $options['headers']['Authorization'] = 'Bearer ' . $this->authData->userAccessToken;
        $res = $this->httpClient->request($method, $uri, $options);

        if ($res->getStatusCode() === 401) {
            throw new $this->authException(fsp__('Threads authentication failed'));
        }

        return $res;
    }

    /**
     * @throws Exception
     */
    private function decodeBodyFromResponse(ResponseInterface $res): array
    {
        $body = json_decode($res->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if (isset($body['error']['error_user_msg']) || isset($body['error']['error_user_title'])) {
            $title = $body['error']['error_user_title'] ?? '';
            $msg = $body['error']['error_user_msg'] ?? '';
            throw new Exception(trim($title . ' ' . $msg));
        }

        if (isset($body['error']['message'])) {
            throw new Exception($body['error']['message']);
        }

        if (isset($body['error'])) {
            throw new Exception(json_encode($body));
        }

        return $body;
    }
}
