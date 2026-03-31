<?php

namespace FSPoster\App\SocialNetworks\Instagram\Api\CookieMethod;

use Exception;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Schedules\ScheduleResponseObject;
use FSPoster\App\SocialNetworks\Instagram\Api\PostingData;
use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Cookie\CookieJar;

class Api
{
    private ?Client $_client = null;
	public AuthData $authData;
	public ?string  $proxy = null;

	public string $authException = \Exception::class;
	public string $postException = \Exception::class;

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy = $proxy;

		return $this;
	}

	public function setAuthData ( AuthData $authData ): self
	{
		$this->authData = $authData;

		return $this;
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

    public function getClient() : Client
    {
		if( is_null( $this->_client ) )
		{
			$this->_client = new Client( [
				'cookies'         => new CookieJar( false, $this->authData->cookies ),
				'allow_redirects' => [ 'max' => 10 ],
				'proxy'           => empty( $this->proxy ) ? null : $this->proxy,
				'verify'          => false,
				'http_errors'     => false,
				'headers'         => [
					'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'
				]
			] );
		}

        return $this->_client;
    }

    public function sendPost ( PostingData $postingData ) : ScheduleResponseObject
    {
        if( $postingData->edge === 'story' )
	        return $this->sendToStory( $postingData );
        else
            return $this->sendToTimeline( $postingData );
    }

    private function sendToTimeline( PostingData $postingData ) : ScheduleResponseObject
    {
        $snPostResponse = new ScheduleResponseObject();

        if (count( $postingData->uploadMedia ) === 1) {
            if( $postingData->uploadMedia[0]['type'] === 'image' ) {
                $response = $this->uploadPhoto( $postingData->uploadMedia[0], $postingData->message );
            } else {
                $response = $this->uploadVideo( $postingData->uploadMedia[0], $postingData->message );
            }
        } else {
            $response = $this->generateAlbum( $postingData->uploadMedia, $postingData->message );
        }

        $snPostResponse->status = 'success';
        $snPostResponse->remote_post_id = $response['id2'];
        $snPostResponse->data = [
            'url' => 'https://instagram.com/p/' . $response['id']
        ];

	    $ids     = explode( '_', $response[ 'id2' ] );
	    $mediaId = count( $ids ) > 1 ? $ids[ 0 ] : $response[ 'id2' ];

        if( ! empty( $mediaId ) && $postingData->edge !== 'story' && ! empty( $postingData->firstComment ) )
            $this->writeComment( $postingData->firstComment, $mediaId );

        return $snPostResponse;
    }

    private function sendToStory( PostingData $postingData ) : ScheduleResponseObject
    {
        $res = $this->uploadPhoto( $postingData->uploadMedia[0], $postingData->message, '', 'story' );

        $snPostResponse = new ScheduleResponseObject();
        $snPostResponse->status = 'success';
        $snPostResponse->remote_post_id = $res['id2'];

        return $snPostResponse;
    }

    public function uploadCarouselItem ( $photo ) : array
    {
        $uploadId = $this->createUploadId();

        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string)$photo[ 'height' ],
            'upload_media_width'  => (string)$photo[ 'width' ],
            'upload_id'           => $uploadId,
        ];

        try
        {
            $response = (string)$this->getClient()->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();

            $result = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
	        $this->handleError( $e->getMessage() );
        }

	    if ( $result[ 'status' ] == 'fail' )
		    throw new $this->postException( $result[ 'message' ] ?? 'Error' );

	    return $result;
    }

    public function uploadVideoForCarousel ( $video )
    {
        $uploadId = $this->createUploadId();

        $params = [
            'client-passthrough' => '1',
            'is_clips_video' => '1',
            'is_sidecar' => '0',
            'media_type' => 2,
            'for_album' => false,
            'video_format'             => 'video/mp4',
            'upload_id'                => $uploadId,
            'upload_media_duration_ms' => (string)( $video[ 'duration' ] * 1000 ),
            'upload_media_height'      => (string)$video[ 'height' ],
            'upload_media_width'       => (string)$video[ 'width' ],
            'video_transform' => NULL,
            'video_edit_params' => []
        ];

        try
        {
            $response = $this->getClient()->post( 'https://i.instagram.com/rupload_igvideo/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken' => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name' => 'fb_uploader_' . $uploadId,
                    'X-Entity-Length' => filesize( $video[ 'path' ] ),
                    'X-Asbd-Id' => '359341',
                    'X-IG-App-ID' => '936619743392459',
                    'x-instagram-ajax' => '1025662345',
                    'Offset' => '0',
                ],
                'body'    => fopen( $video[ 'path' ], 'r' )
            ] )->getBody();
        }
        catch ( Exception $e )
        {
            $this->handleError( $e->getMessage() );
        }

        $response = json_decode( $response, true );

        if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
            $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

        $videoThumbnail = $video[ 'thumbnail' ];

        $params = [
            'media_type'          => '2',
            'upload_media_height' => (string)$videoThumbnail[ 'height' ],
            'upload_media_width'  => (string)$videoThumbnail[ 'width' ],
            'upload_id'           => $uploadId
        ];

        try
        {
            $response = $this->getClient()->post( 'https://i.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'fb_uploader_' . $uploadId,
                    'X-Entity-Length'            => filesize( $videoThumbnail[ 'path' ] ),
                    'Offset'                     => '0',
                ],
                'body'    => fopen( $videoThumbnail[ 'path' ], 'r' )
            ] );
        }
        catch ( Exception $e )
        {
            $this->handleError( $e->getMessage() );
        }

        return json_decode( $response->getBody(), true );
    }


    public function generateAlbum ($media, $caption ) : array
    {
        $body = [
            "caption"                       => $caption,
            "children_metadata"             => [],
            "client_sidecar_id"             => $this->createUploadId(),
            "disable_comments"              => "0",
            "like_and_view_counts_disabled" => false,
            "source_type"                   => "library"
        ];

        foreach ($media as $medium )
        {
            if ($medium[ 'type' ] === 'image' ) {
                $response = $this->uploadCarouselItem( $medium );
            } else {
                $response = $this->uploadVideoForCarousel( $medium );
            }

            $body[ "children_metadata" ][] = [
                "upload_id" => $response[ 'upload_id' ]
            ];
        }

        if ( count( $body[ 'children_metadata' ] ) == 0 )
	        throw new $this->postException( 'Error' );

        try
        {
            for ($i = 0; $i < 30; $i++) {
                $response = (string)$this->getClient()->post( "https://i.instagram.com/api/v1/media/configure_sidecar/", [
                    'headers' => [
                        'X-Requested-With' => 'XMLHttpRequest',
                        'X-CSRFToken'      => $this->getCsrfToken(),
                        'Offset'           => '0',
                        "x-ig-app-id"      => "936619743392459",
                        "x-csrf-token"     => $this->getCsrfToken()
                    ],
                    "json"    => $body
                ] )->getBody();

                $result = json_decode( $response, true );

                if ( isset( $result[ 'error_id' ] ) )
                    $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

                if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'ok' )
                    break;
                sleep(3);
            }
        }
        catch ( Exception $e )
        {
			$this->handleError( $e->getMessage() );
        }

	    if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
		    $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

	    return [
		    'id'     => $result[ 'media' ][ 'code' ] ?? '?',
		    'id2'    => $result[ 'media' ][ 'id' ] ?? '?'
	    ];
    }

    public function uploadPhoto ( $photo, $caption, $link = '', $target = 'feed' ) : array
    {
        $uploadId = $this->createUploadId();

        $params = [
            'media_type'          => '1',
            'upload_media_height' => (string)$photo[ 'height' ],
            'upload_media_width'  => (string)$photo[ 'width' ],
            'upload_id'           => $uploadId,
        ];

        try
        {
            $response = (string)$this->getClient()->post( 'https://www.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'feed_' . $uploadId,
                    'X-Entity-Length'            => filesize( $photo[ 'path' ] ),
                    'Offset'                     => '0'
                ],
                'body'    => fopen( $photo[ 'path' ], 'r' )
            ] )->getBody();
        }
        catch ( Exception $e )
        {
	        $this->handleError( $e->getMessage() );
        }

        $response = json_decode( $response, true );

        if ( ! isset( $response[ 'upload_id' ] ) || $response[ 'upload_id' ] != $uploadId )
			$this->handleError( $response[ 'message' ] ?? ( $response[ 'debug_info' ][ 'message' ] ?? '' ) );

        switch ( $target )
        {
            case 'feed':
                $endpoint = 'configure';

                $params = [
                    'upload_id'                    => $uploadId,
                    'caption'                      => $caption,
                    'usertags'                     => '',
                    'custom_accessibility_caption' => '',
                    'retry_timeout'                => ''
                ];
                break;
            default:
                $endpoint = 'configure_to_story';

                $params = [
                    'upload_id' => $uploadId,
                    'story_cta' => json_encode( [ [ "links" => [ [ "webUri" => $link ] ] ] ] )
                ];
        }

        try
        {
            $result = (string)$this->getClient()->post( 'https://www.instagram.com/create/' . $endpoint . '/', [
                'form_params' => $params,
                'headers'     => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken()
                ]
            ] )->getBody();
        }
        catch ( Exception $e )
        {
			$this->handleError( $e->getMessage() );
        }

        $result = json_decode( $result, true );

        if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
			$this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

        return [
            'id'     => $result[ 'media' ][ 'code' ] ?? '?',
            'id2'    => $result[ 'media' ][ 'id' ] ?? '?'
        ];
    }

    public function uploadVideo ( $video, $caption ) : array
    {
        $uploadId = $this->createUploadId();

        $params = [
            'client-passthrough' => '1',
            'is_clips_video' => '1',
            'is_sidecar' => '0',
            'media_type' => 2,
            'for_album' => false,
            'video_format'             => 'video/mp4',
            'upload_id'                => $uploadId,
            'upload_media_duration_ms' => (string)( $video[ 'duration' ] * 1000 ),
            'upload_media_height'      => (string)$video[ 'height' ],
            'upload_media_width'       => (string)$video[ 'width' ],
            'video_transform' => NULL,
            'video_edit_params' => []
        ];

        try
        {
            $response = $this->getClient()->post( 'https://i.instagram.com/rupload_igvideo/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken' => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name' => 'fb_uploader_' . $uploadId,
                    'X-Entity-Length' => filesize( $video[ 'path' ] ),
                    'X-Asbd-Id' => '359341',
                    'X-IG-App-ID' => '936619743392459',
                    'x-instagram-ajax' => '1025662345',
                    'Offset' => '0',
                ],
                'body'    => fopen( $video[ 'path' ], 'r' )
            ] )->getBody();
        }
        catch ( Exception $e )
        {
			$this->handleError( $e->getMessage() );
        }

        $response = json_decode( $response, true );

        if ( isset( $response[ 'status' ] ) && $response[ 'status' ] == 'fail' )
	        $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

        $videoThumbnail = $video[ 'thumbnail' ];

        $params = [
            'media_type'          => '2',
            'upload_media_height' => (string)$videoThumbnail[ 'height' ],
            'upload_media_width'  => (string)$videoThumbnail[ 'width' ],
            'upload_id'           => $uploadId
        ];

        try
        {
            $response = $this->getClient()->post( 'https://i.instagram.com/rupload_igphoto/fb_uploader_' . $uploadId, [
                'headers' => [
                    'X-Requested-With'           => 'XMLHttpRequest',
                    'X-CSRFToken'                => $this->getCsrfToken(),
                    'X-Instagram-Rupload-Params' => json_encode( $params ),
                    'X-Entity-Name'              => 'fb_uploader_' . $uploadId,
                    'X-Entity-Length'            => filesize( $videoThumbnail[ 'path' ] ),
                    'Offset'                     => '0',
                ],
                'body'    => fopen( $videoThumbnail[ 'path' ], 'r' )
            ] );
        }
        catch ( Exception $e )
        {
	        $this->handleError( $e->getMessage() );
        }

        try
        {
            for ($i = 0; $i < 30; $i++) {
                $result = (string)$this->getClient()->post( 'https://www.instagram.com/api/v1/media/configure_to_clips/', [
                    'form_params' => [
                        'archive_only' => false,
                        'clips_share_preview_to_feed' => '1',
                        'disable_comments' => '0',
                        'disable_oa_reuse' => false,
                        'igtv_share_preview_to_feed' => '1',
                        'is_meta_only_post' => '0',
                        'is_unified_video' => '1',
                        'like_and_view_counts_disabled' => '0',
                        'media_share_flow' => 'creation_flow',
                        'share_to_facebook' => '',
                        'share_to_fb_destination_type' => 'USER',
                        'source_type' => 'library',
                        'video_subtitles_enabled' => '0',
                        'upload_id' => $uploadId,
                        'caption' => $caption,
                    ],
                    'headers'     => [
                        'X-Asbd-Id' => '359341',
                        'X-Requested-With' => 'XMLHttpRequest',
                        'X-CSRFToken'      => $this->getCsrfToken(),
                        'X-Ig-App-Id'       => '936619743392459',
                        'x-requested-with' => 'XMLHttpRequest',
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36'
                    ]
                ] )->getBody();
                $result = json_decode( $result, true );

                if ( isset( $result[ 'error_id' ] ) )
                    $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

                if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'ok' )
                    break;
                sleep(3);
            }
        }
        catch ( Exception $e )
        {
	        $this->handleError( $e->getMessage() );
        }

        if ( isset( $result[ 'status' ] ) && $result[ 'status' ] == 'fail' )
	        $this->handleError( $result[ 'message' ] ?? ( $result[ 'debug_info' ][ 'message' ] ?? '' ) );

        return [
            'id'     => $result[ 'media' ][ 'code' ] ?? '?',
            'id2'    => $result[ 'media' ][ 'id' ] ?? '?'
        ];
    }

	public function getMe () : array
	{
		try
		{
			$response = (string)$this->getClient()->get( 'https://www.instagram.com/' )->getBody();
		}
		catch ( Exception $e )
		{
			throw new $this->authException( $e->getMessage() );
		}

		preg_match( '/\"id\":\"([0-9]+)\"/iU', $response, $id );
		preg_match( '/profile_pic_url\":\"(.+)\"/iU', $response, $profile_pic_url );
		preg_match( '/username\":\"(.+)\"/iU', $response, $username );
		preg_match( '/csrf_token\":\"(.+)\"/iU', $response, $csrfToken );

		if ( isset( $profile_pic_url[ 1 ] ) )
			$profile_pic_url = str_replace( ['\\\\u0026', '\/'], ['&', '/'], $profile_pic_url[ 1 ] );
		else
			$profile_pic_url = '';

		if ( empty( $id ) || empty( $username ) || empty( $csrfToken ) )
			throw new $this->authException('Could not fetch account information');

		$this->authData->cookies[] = [
			"Name"     => "mcd",
			"Value"    => "3",
			"Domain"   => ".instagram.com",
			"Path"     => "/",
			"Max-Age"  => null,
			"Expires"  => null,
			"Secure"   => true,
			"Discard"  => false,
			"HttpOnly" => true
		];

		return [
			'id'                    => $id[1],
			'name'                  => json_decode( '"' . str_replace( '"', '\\"', $username[ 1 ] ) . '"' ),
			'profile_picture_url'   => $profile_pic_url,
			'username'              => $username[1]
		];
	}

    public function getStats ( $postId ) : array
    {
        try
        {
			$options = [
				'headers'     => [
					'X-Requested-With'  => 'XMLHttpRequest',
					'X-CSRFToken'       => $this->getCsrfToken(),
					'X-IG-App-ID'       => '936619743392459',
				]
			];
            $response = (string)$this->getClient()->get( 'https://www.instagram.com/api/v1/media/'.$postId.'/info', $options )->getBody();
        }
        catch ( Exception $e )
        {
            $response = '{}';
        }

	    $response = json_decode( $response, true );

		$likesCount = $response['items'][0]['like_count'] ?? 0;
		$commentsCount = $response['items'][0]['comment_count'] ?? 0;

        return [
            [
                'label' => fsp__( 'Comments' ),
                'value' => $commentsCount
            ],
            [
                'label' => fsp__( 'Likes' ),
                'value' => $likesCount
            ],
        ];
    }

    public function writeComment ( $comment, $mediaId ) : string
    {
        $endpoint = sprintf( "https://www.instagram.com/web/comments/%s/add/", $mediaId );

        try
        {
            $response = $this->getClient()->post( $endpoint, [
                "form_params" => [
                    "comment_text"          => $comment,
                    "replied_to_comment_id" => ""
                ],
                'headers'     => [
                    'X-Requested-With' => 'XMLHttpRequest',
                    'X-CSRFToken'      => $this->getCsrfToken()
                ]
            ] )->getBody();

            $response = json_decode( $response, true );
        }
        catch ( Exception $e )
        {
	        throw new $this->postException( 'First comment error: ' . $e->getMessage() );
        }

        if ( ! isset( $response[ 'status' ] ) || $response[ 'status' ] != 'ok' )
	        throw new $this->postException( 'First comment error: ' . ($response[ 'message' ] ?? 'Unknown error') );

        return (string)$response['id'];
    }

	private function handleError ( $errorMsg = null )
	{
		if ( $errObj = json_decode( $errorMsg, true ) )
			$errorMsg = $errObj[ 'message' ] ?? $errorMsg;

		$errorMsg = $errorMsg ?: 'An error occurred while processing the request';

		if ( $errorMsg === 'login_required' )
			throw new $this->authException( 'The account is disconnected from the plugin. Please add your account to the plugin again by getting the cookie on the browser <a href=\'https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-instagram\' target=\'_blank\'>Incognito mode</a>. And close the browser without logging out from the account.' );
		else
			throw new $this->postException( $errorMsg );
	}


    private function getCsrfToken ()
    {
        $cookies = $this->getClient()->getConfig( 'cookies' )->toArray();
        $csrf = '';

        foreach ( $cookies as $cookieInf )
        {
            if ( $cookieInf[ 'Name' ] == 'csrftoken' )
            {
                $csrf = $cookieInf[ 'Value' ];
            }
        }

        return $csrf;
    }

    private function createUploadId () : string
    {
        return Date::epoch() . rand( 100, 999 );
    }

}
