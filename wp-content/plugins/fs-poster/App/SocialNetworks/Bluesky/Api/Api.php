<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Api;

use FSPoster\App\Models\ChannelSession;
use FSPoster\App\Providers\Channels\ChannelService;
use FSPoster\App\Providers\Channels\ChannelSessionException;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Helpers\GuzzleClient;
use FSPoster\App\Providers\Helpers\URLScraper;
use FSPoster\App\Providers\Helpers\WPPostThumbnail;
use FSPoster\App\SocialNetworks\Bluesky\Adapters\ChannelAdapter;
use FSPoster\App\SocialNetworks\Bluesky\Api\Helpers\Helper;

class Api
{
    public AuthData $authData;
    private string $authException;
    private string $postException;
    public $proxy;
    private const MAX_IMAGE_SIZE = 976.56*1024;
    private const MAX_COMPRESSION_COUNT = 50;

    public function setAuthException (string $exceptionClass ): self
    {
        $this->authException = $exceptionClass;

        return $this;
    }

    public function setPostException (string $exceptionClass ): self
    {
        $this->postException = $exceptionClass;

        return $this;
    }

    /**
     * @throws ChannelSessionException
     */
    public function setAuthData (AuthData $authData ): self
    {
        $this->authData = $authData;

        return $this;
    }

    public function setProxy ( $proxy )
    {
        $this->proxy = $proxy;

        return $this;
    }

    public function getMe(): array
    {
        if (empty($this->authData->identifier)) {
            throw new $this->authException( fsp__( 'Missing identifier' ) );
        }

        $rawResponse = $this->getClient()->get("https://public.api.bsky.app/xrpc/app.bsky.actor.getProfile?actor={$this->authData->identifier}");

        $statusCode = $rawResponse->getStatusCode();

        if ($statusCode != 200) {
            throw new $this->authException( fsp__( 'Could not fetch profile data' ) );
        }

        return json_decode( $rawResponse->getBody()->getContents(), true );
    }

    /**
     * @throws ChannelSessionException
     */
    public function createSession(): self
    {
        $rawResponse = $this->getClient()->post( 'https://bsky.social/xrpc/com.atproto.server.createSession', [
            'json' => [
                'identifier' => $this->authData->identifier,
                'password' => $this->authData->appPassword,
            ]
        ] );

        $statusCode = $rawResponse->getStatusCode();

        if ( $statusCode != 200 ) {
            throw new $this->authException( fsp__( 'Invalid Credentials' ) );
        }

        $contents = json_decode( $rawResponse->getBody()->getContents(), true );

        $this->authData->setFromArray( $contents );

        foreach ($contents['didDoc']['service'] as $s) {
            if ($s['id'] === '#atproto_pds') {
                $this->authData->serviceEndpoint = $s['serviceEndpoint'];
                break;
            }
        }

        return $this;
    }

    /**
     * @throws ChannelSessionException
     */
    public function refreshSession(): AuthData
    {
        if ( !isset( $this->authData->refreshJwt ) )
        {
            throw new $this->authException( fsp__( 'Refresh token is not set' ) );
        }

        if (Helper::isJWTExpired($this->authData->refreshJwt, 100))
        {
            $this->createSession();
            return $this->authData;
        }

        $rawResponse = $this->getClient()->post( 'https://bsky.social/xrpc/com.atproto.server.refreshSession', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authData->refreshJwt,
            ],
        ]);

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ($statusCode == 200) {
            $this->authData->setFromArray($response);
        }
        else {
            throw new $this->authException( fsp__( 'Could not refresh session' ) );
        }

        return $this->authData;
    }

    private function getClient() : GuzzleClient
    {
        return new GuzzleClient([
            'proxy' => $this->proxy ?? null,
            'verify' => false,
            'headers' => [ 'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0' ],
        ]);
    }

    /**
     * @param PostingData $postingData
     * @return mixed
     * @throws \Exception
     */
    public function sendPost(PostingData $postingData)
    {
        if ( !isset( $this->authData->accessJwt ) ) {
            throw new $this->authException( fsp__( 'Access token is not set' ) );
        }

        $embed = $this->createEmbedIfNeeded($postingData);
        $response = $this->createRecord($postingData, $embed);
        $this->createFirstCommentIfNeeded($postingData, $response);

        return $response;
    }

    public function getStats( string $uri ) : array
    {
        return [
            [
                'label' => fsp__('Reposts'),
                'value' => $this->getRepostStats($uri)
            ],
            [
                'label' => fsp__( 'Quotes' ),
                'value' => $this->getQuoteStats($uri)
            ],
            [
                'label' => fsp__( 'Likes' ),
                'value' => $this->getLikeStats($uri)
            ],
            [
                'label' => fsp__( 'Replies' ),
                'value' => $this->getReplyStats($uri)
            ]
        ];
    }

    private function createRecord(PostingData $postingData, ?array $embed = null)
    {
        $body = [
            'repo' => $this->authData->identifier,
            'collection' => $postingData->collection,
            'record' => [
                '$type' => $postingData->type,
                'text' => $postingData->message,
                'createdAt' => $postingData->createdAt,
            ]
        ];

        if ( !empty( $linkFacets = $this->makeLinksClickable($postingData->message) ) ) {
            $body['record']['facets'] = $linkFacets;
        }

        if ( !empty( $tagFacets = $this->createTags($postingData->message) ) ) {
            $body['record']['facets'] = $tagFacets;
        }

        if ( !empty( $embed ) ) {
            $body['record']['embed'] = $embed;
        }

        $rawResponse = $this->getClient()->post( 'https://bsky.social/xrpc/com.atproto.repo.createRecord', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authData->accessJwt
            ],
            'json' => $body,
        ]);

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }

        return $response;
    }

    private function createEmbedIfNeeded(PostingData $postingData): ?array
    {
        $embed = null;

        if( !empty( $postingData->uploadMedia ) && $postingData->uploadMedia[0]['type'] === 'image' ) {
            $embed = $this->uploadBlobs( $postingData );
        }
        else if( !empty( $postingData->uploadMedia ) && $postingData->uploadMedia[0]['type'] === 'video' ) {
            $embed = $this->createVideoPost($postingData);
        }
        else if ( ! empty( $postingData->link ) ) {
            $embed = $this->attachLink($postingData);
        }

        return $embed;
    }

    private function createFirstCommentIfNeeded( PostingData $postingData, array $parentResponse )
    {
        if (empty($postingData->firstComment))
        {
            return;
        }

        $body = [
            'repo' => $this->authData->identifier,
            'collection' => $postingData->collection,
            'record' => [
                '$type' => $postingData->type,
                'text' => $postingData->firstComment,
                'createdAt' => Date::format('Y-m-d\TH:i:s\Z'), // If the createdAt field for both a post and its reply is the same,
                                                                        // the post will not appear in the "Posts" tab but only in the "Replies" tab.
                'reply' => [
                    'root' => [
                        'cid' => $parentResponse['cid'],
                        'uri' => $parentResponse['uri']
                    ],
                    'parent' => [
                        'cid' => $parentResponse['cid'],
                        'uri' => $parentResponse['uri']
                    ]
                ]
            ]
        ];

        if ( !empty( $linkFacets = $this->makeLinksClickable($postingData->firstComment) ) ) {
            $body['record']['facets'] = $linkFacets;
        }

        if ( !empty( $tagFacets = $this->createTags($postingData->message) ) ) {
            $body['record']['facets'] = $tagFacets;
        }

        $rawResponse = $this->getClient()->post( 'https://bsky.social/xrpc/com.atproto.repo.createRecord', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authData->accessJwt
            ],
            'json' => $body,
        ]);

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }
    }

    private function getRepostStats( string $uri ) : int
    {
        $rawResponse = $this->getClient()->get( 'https://public.api.bsky.app/xrpc/app.bsky.feed.getRepostedBy/?uri=' . $uri );

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new \Exception( $response['message'] );
        }

        return count($response['repostedBy']);
    }

    private function getQuoteStats( string $uri ) : int
    {
        $rawResponse = $this->getClient()->get( 'https://public.api.bsky.app/xrpc/app.bsky.feed.getQuotes/?uri=' . $uri );

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }

        return count($response['posts']);
    }

    private function getLikeStats( string $uri ) : int
    {
        $rawResponse = $this->getClient()->get( 'https://public.api.bsky.app/xrpc/app.bsky.feed.getLikes/?uri=' . $uri );

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }

        return count($response['likes']);
    }

    private function getReplyStats( string $uri ) : int
    {
        $rawResponse = $this->getClient()->get( 'https://public.api.bsky.app/xrpc/app.bsky.feed.getPostThread/?uri=' . $uri );

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }

        return count($response['thread']['replies'] ?? []);
    }

    private function uploadBlobs(PostingData $postingData)
    {
        $embed = [
            '$type' => 'app.bsky.embed.images',
            'images' => []
        ];

        foreach ( $postingData->uploadMedia as $media )
        {
            $rawResponse = $this->getClient()->post('https://bsky.social/xrpc/com.atproto.repo.uploadBlob', [
                'headers' => [
                    'Authorization' => 'Bearer '. $this->authData->accessJwt,
                    'Content-Type' => $media['mimeType'],
                ],
                'body' => fopen($media['path'], 'r'),
            ]);

            $statusCode = $rawResponse->getStatusCode();
            $response = json_decode( $rawResponse->getBody()->getContents(), true );

            if ( $statusCode != 200 ) {
                throw new $this->postException( $response['message'] );
            }

            $embed['images'][] = [
                'alt' => $media['alt'],
                'image' => $response['blob']
            ];
        }

        return $embed;
    }

    private function createVideoPost(PostingData $postingData): array
    {
        $serviceToken = $this->getServiceAuth();
        $jobId = $this->uploadVideo($postingData, $serviceToken);
        $blob = $this->getJobStatus($jobId);

        return [
            '$type' => 'app.bsky.embed.video',
            'video' => $blob,
            'aspectRatio' => ['width' => 16, 'height' => 9]
        ];
    }

    private function getServiceAuth()
    {
        $aud = 'did:web:' . parse_url($this->authData->serviceEndpoint, PHP_URL_HOST);
        $lxm = 'com.atproto.repo.uploadBlob';

        $response = $this->getClient()->get("https://bsky.social/xrpc/com.atproto.server.getServiceAuth/?aud={$aud}&lxm={$lxm}", [
            'headers' => ['Authorization' => "Bearer {$this->authData->accessJwt}"],
        ]);

        return json_decode($response->getBody()->getContents(), true)['token'];
    }

    private function uploadVideo(PostingData $postingData, string $serviceToken)
    {
        $did = $this->authData->did;
        $media = $postingData->uploadMedia[0];

        $rawResponse = $this->getClient()->post("https://video.bsky.app/xrpc/app.bsky.video.uploadVideo?did=$did&name={$media['name']}", [
            'headers' => [
                'Authorization' => "Bearer $serviceToken",
                'Content-Type' => 'video/mp4',
                'Content-Length' => filesize($media['path']),
            ],
            'body' => fopen($media['path'], 'r')
        ]);

        $response = json_decode($rawResponse->getBody()->getContents(), true);

        if ( !empty($response['error']) && !(!empty($response['state']) && $response['state'] == 'JOB_STATE_COMPLETED') ) {
            throw new $this->postException( $response['error'] );
        }

        return $response['jobId'];
    }

    private function getJobStatus(string $jobId)
    {
        $counter = 0;

        do {
            sleep(3);
            $counter++;

            $response = $this->getClient()->get("https://video.bsky.app/xrpc/app.bsky.video.getJobStatus?jobId=$jobId");

            $status = json_decode($response->getBody()->getContents(), true);

            if ($status['jobStatus']['state'] === 'JOB_STATE_COMPLETED')
            {
                return $status['jobStatus']['blob'];
            }
        } while ($counter < 20);

        return null;
    }

    private function attachLink(PostingData $postingData): ?array
    {
        $scrapeData = $this->scrapeURL($postingData->link);

        if (!isset($scrapeData['blob'])) {
            return null;
        }

        return [
            '$type' => 'app.bsky.embed.external',
            'external' => [
                'uri' => $postingData->link,
                'title' => $scrapeData['title'],
                'description' => $scrapeData['description'],
                'thumb' => $scrapeData['blob'],
            ]
        ];
    }

    private function scrapeURL ( $url ): array
    {
        $scrapeData = [
            'source' => $url,
        ];

        $scraped = URLScraper::scrape( $url );

        $scrapeData['title']       = $scraped['title'];
        $scrapeData['description'] = $scraped['description'];

        if ( !empty( $scraped['image'] ) )
        {
            $image = WPPostThumbnail::saveRemoteImage( $scraped['image'] );

            if ( $image !== false )
            {
                $blob = $this->uploadScrapedURLImage( $image, $scrapeData['title'] );
                $scrapeData['blob'] = $blob;
            }

        }

        return $scrapeData;
    }

    private function compress(string $source, string $destination, int $quality = 75)
    {
        if (!file_exists($source)) {
            return false;
        }

        $info = getimagesize($source);
        if ($info === false || empty($info['mime'])) {
            return false;
        }

        $createMap = [
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png'  => 'imagecreatefrompng',
            'image/gif'  => 'imagecreatefromgif',
            'image/webp' => 'imagecreatefromwebp',
        ];

        if (!isset($createMap[$info['mime']])) {
            return false;
        }

        $image = $createMap[$info['mime']]($source);

        if (!$image) {
            return false;
        }

        if ($info['mime'] === 'image/png') {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        $newWidth = (int) ($info[0] * $quality / 100);
        $image = imagescale($image, $newWidth, -1, IMG_BICUBIC);

        switch ($info['mime']) {
            case 'image/jpeg':
                $result = imagejpeg($image, $destination, $quality);
                break;

            case 'image/png':
                $pngQuality = (int) round((100 - $quality) / 10);
                $result = imagepng($image, $destination, $pngQuality);
                break;

            case 'image/gif':
                $result = imagegif($image, $destination);
                break;

            case 'image/webp':
                $result = imagewebp($image, $destination, $quality);
                break;
        }

        imagedestroy($image);

        return $result ? $destination : false;
    }


    private function resizeToRequired(string $source)
    {
        $size = filesize($source);

        if (empty($size)) {
            return false;
        }

        if ($size < self::MAX_IMAGE_SIZE) {
            return $source;
        }

        $counter = 0;
        $tmpOut = stream_get_meta_data(tmpfile())['uri'];
        while ($size >= self::MAX_IMAGE_SIZE && $counter < self::MAX_COMPRESSION_COUNT) {
            $quality = 40 + ((self::MAX_COMPRESSION_COUNT - $counter) / self::MAX_IMAGE_SIZE) * 30;
            $compressed = $this->compress($source, $tmpOut, $quality);
            if ($compressed === false) {
                break;
            }

            $size = filesize($compressed);
            $counter++;
        }

        return $size < self::MAX_IMAGE_SIZE ? $tmpOut : false;
    }

    private function uploadScrapedURLImage(string $image, string $alt)
    {
        $resized = $this->resizeToRequired($image);

        if (!$resized) {
            return null;
        }


        $rawResponse = $this->getClient()->post('https://bsky.social/xrpc/com.atproto.repo.uploadBlob', [
            'headers' => [
                'Authorization' => 'Bearer '. $this->authData->accessJwt,
                'Content-Type' => 'image/jpeg',
            ],
            'body' => fopen($resized, 'r'),
        ]);

        $statusCode = $rawResponse->getStatusCode();
        $response = json_decode( $rawResponse->getBody()->getContents(), true );

        if ( $statusCode != 200 ) {
            throw new $this->postException( $response['message'] );
        }

        return $response['blob'];
    }

    private function makeLinksClickable(string $text): array
    {
        $pattern = '~(?:@(?!(?:https?:\/\/))|(?<!\w))(?:https?:\/\/)?(?:[a-z0-9-]+)+(?:\.\w+)+(?:[/?][^\s<>"\'()]*)?~i';

        preg_match_all($pattern, $text, $m, PREG_OFFSET_CAPTURE);

        $facets = [];

        foreach ($m[0] as [$match, $pos]) {
            $match = preg_replace('/[\s\x{00A0}\x{200B}-\x{200D}\x{FEFF}]+$/mu', '', $match);
            $end = (int)$pos + strlen($match);

            $prefix = strpos($match, 'http') === false ? 'https://' : '';

            $facets[] = [
                'index' => [
                    'byteStart' => $pos,
                    'byteEnd' => $end
                ],
                'features' => [
                    [
                        '$type' => 'app.bsky.richtext.facet#link',
                        'uri' => $prefix . $match
                    ]
                ]
            ];
        }

        return $facets;
    }

    private function createTags(string $text): array
    {
        $pattern = '/#\p{L}+(?:[-_]*[\p{L}\p{N}])*/u';

        preg_match_all($pattern, $text, $m, PREG_OFFSET_CAPTURE);

        $facets = [];

        foreach ($m[0] as [$match, $pos]) {
            $end = (int)$pos + strlen($match);

            $facets[] = [
                'index' => [
                    'byteStart' => $pos,
                    'byteEnd' => $end
                ],
                'features' => [
                    [
                        '$type' => 'app.bsky.richtext.facet#tag',
                        'tag' => mb_substr($match, 1, null, 'UTF-8')
                    ]
                ]
            ];
        }

        return $facets;
    }
}