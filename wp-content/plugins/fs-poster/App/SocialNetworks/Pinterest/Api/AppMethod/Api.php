<?php

namespace FSPoster\App\SocialNetworks\Pinterest\Api\AppMethod;

use Exception;
use FSPoster\App\Providers\Helpers\Curl;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\App\Providers\Helpers\Helper;
use FSPoster\App\SocialNetworks\Pinterest\Api\PostingData;
use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Exception\GuzzleException;

class Api
{

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

		$this->refreshAccessTokenIfNeed();

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

    /**
     * @throws Exception|GuzzleException
     */
    public function sendPost ( PostingData $postingData ): string
    {
        $uploadIds = [];

        foreach ($postingData->uploadMedia as $media) {
            try {
                if ($media['type'] === 'image') {
                    $uploadIds[] = $this->uploadPhoto( $postingData, $media['path'] );
                } else {
                    $uploadIds[] = $this->uploadVideo( $postingData, $media['path'] );
                }
            } catch (Exception $e) {
                if (!count($uploadIds)) { // if the first media upload was unsuccessful
                    throw new Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $uploadIds[0];
    }

    private function uploadPhoto ( PostingData $postingData, string $path ) : string
    {
		if ( function_exists( 'getimagesize' ) )
		{
			$result = @getimagesize( $path );

			if ( isset( $result[0], $result[1] ) )
			{
				$width  = $result[0];
				$height = $result[1];

				if ( $width < 200 || $height < 300 )
				{
					throw new $this->postException( fsp__( 'Pinterest supports images bigger than 200x300. Your image is %sx%s.', [
						$width,
						$height,
					] ) );
				}
			}
		}

		$mimeType = Helper::mimeContentType( $path );

		$fileContent = false;

		if ( strpos( $mimeType, 'webp' ) !== false )
		{
			$fileContent = Helper::webpToJpg( $path );
		}

		if ( $fileContent === false )
			$fileContent = file_get_contents( $path );
		else
			$mimeType = 'image/png';

		$result = $this->apiRequest( 'pins', 'POST', $this->createSendData($postingData, [
                'source_type' => 'image_base64',
                'content_type' => $mimeType,
                'data' => base64_encode( $fileContent )
        ]))['data'];

		if( ! isset( $result[ 'id' ] ) )
			throw new $this->postException( $result['error']['message'] ?? ( $result['message'] ?? 'Error' ) );

		return (string)$result['id'];
	}

    /**
     * @param   PostingData  $postingData
     * @param   string       $path
     *
     * @return string
     *
     * @throws GuzzleException
     * @link https://developers.pinterest.com/docs/work-with-organic-content-and-users/create-boards-and-pins/#creating-video-pins
     */
    private function uploadVideo(PostingData $postingData, string $path): string
    {
        // Step 1: Register your intent to upload a video
        $videoUploadRegisterResult = $this->apiRequest( 'media', 'POST', [
                'media_type'  => 'video',
        ] );

        if ($videoUploadRegisterResult['status_code'] > 201) {
            throw new $this->postException( $videoUploadRegisterResult['message'] );
        }

        [
            'media_id' => $mediaId,
            'upload_url' => $uploadUrl,
            'upload_parameters' => $uploadParameters
        ] = $videoUploadRegisterResult['data'];

        // Step 2: Upload the video file to the Pinterest Media AWS bucket
        $multipartUploadedParameters = array_map(function ($k) use ($uploadParameters) {
                return ['name' => $k, 'contents' => $uploadParameters[$k]];
            },array_keys($uploadParameters)
        );

        $client = new Client();

        $multipartParams = array_merge($multipartUploadedParameters, [[
            'name'     => 'file',
            'contents' => file_get_contents($path),
            'filename' => basename($path),
        ]]);

        try {
            $awsResult = $client->post($uploadUrl, [
                'multipart' => $multipartParams
            ]);
        } catch (GuzzleException $e) {
            if ( method_exists( $e, 'getResponse' ) && ! empty( $e->getResponse() ) )
                throw new $this->postException( $e->getResponse()->getBody()->getContents() );

            throw new $this->postException( $e->getMessage() );
        }

        $awsStatusCode = $awsResult->getStatusCode();

        if ($awsStatusCode != 204) {
            throw new $this->postException(fsp__('Failed to upload video!'));
        }

        // Step 3: Confirm upload
        $tries = 0;
        while($tries < 30) {
            $confirmResult = $this->apiRequest("media/{$mediaId}", 'GET', []);

            if($confirmResult['status_code'] == 200 && $confirmResult['data']['status'] == 'succeeded') {
                break;
            }

            sleep(3);

            $tries++;
        }

        // Step 4: Create Pin
        $pinCreateResult = $this->apiRequest( 'pins', 'POST', $this->createSendData($postingData, [
            'source_type' => 'video_id',
            'cover_image_key_frame_time' => 0,
            'media_id' => $mediaId
        ]));

        if($pinCreateResult['status_code'] > 201) {
            throw new $this->postException( $pinCreateResult['message'] );
        }

        return (string)$pinCreateResult['data']['id'];
    }

    private function createSendData(PostingData $postingData, array $mediaSource): array
    {
        return [
            'board_id'     => $postingData->boardId,
            'title'        => $postingData->title,
            'description'  => $postingData->message,
//            'link'         => $postingData->link,  // not required
            'alt_text'     => $postingData->altText,
            'media_source' => $mediaSource
        ];
    }

    /**
     * @param   string  $endpoint
     * @param   string  $HTTPMethod
     * @param   array   $data
     *
     * @return array{
     *    data: mixed,
     *    status_code: int
     *  }
     */
	public function apiRequest ( string $endpoint, string $HTTPMethod, array $data = [] ): array
    {
		$options = [];
		//$data[ 'access_token' ] = $accessToken;

		$url = 'https://api.pinterest.com/v5/' . trim( $endpoint, '/' ) . '/';

		$method = $HTTPMethod === 'POST' ? 'POST' : ( $HTTPMethod === 'DELETE' ? 'DELETE' : 'GET' );

		$options[ 'headers' ] = [
			'Authorization' => 'Bearer ' . $this->authData->accessToken,
		];

		if ( $method === 'POST' )
		{
			$options[ 'headers' ][ 'Content-Type' ] = 'application/json';
			$data                                   = json_encode( $data );
			$options[ 'body' ]                      = $data;
		}
		else if ( ! empty( $data ) )
		{
			$options[ 'query' ] = $data;
		}

		$client = new Client();

		try
		{
            $result = $client->request( $method, $url, $options );

            $statusCode = $result->getStatusCode();
			$data = json_decode($result->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			if ( method_exists( $e, 'getResponse' ) && ! empty( $e->getResponse() ) )
				throw new $this->postException( $e->getResponse()->getBody()->getContents() );

			throw new $this->postException( $e->getMessage() );
        }

		if ( ! is_array( $data ) )
			throw new $this->postException( 'Error' );

		return ['data' => $data, 'status_code' => $statusCode];
	}

	public function fetchAccessToken ( $code, $callbackUrl ) : Api
    {
        $appSecret = urlencode( $this->authData->appSecret );
		$appId     = urlencode( $this->authData->appId );

		$token_url = "https://api.pinterest.com/v5/oauth/token";

		$response = Curl::getContents( $token_url, 'POST', [
			'grant_type'   => 'authorization_code',
			'code'         => $code,
			'redirect_uri' => $callbackUrl,
		], [
			'Authorization' => 'Basic ' . base64_encode( $appId . ':' . $appSecret ),
			'Content-Type'  => 'application/x-www-form-urlencoded',
		], $this->proxy, true );

		$params = json_decode( $response, true );

		if ( isset( $params[ 'message' ] ) )
            throw new $this->authException( $params['message'] );

		$this->authData->accessToken = $params['access_token'];
		$this->authData->accessTokenExpiresOn = Date::dateTimeSQL( 'now', '+' . (int) $params['expires_in'] . ' seconds' );
		$this->authData->refreshToken = $params['refresh_token'];

        return $this;
	}

	private function refreshAccessTokenIfNeed ()
	{
		if ( ! empty( $this->authData->accessTokenExpiresOn ) && ( Date::epoch() + 30 ) > Date::epoch( $this->authData->accessTokenExpiresOn ) )
		{
			$this->refreshAccessToken();
		}
	}

    private function refreshAccessToken ()
	{
        $token_url = "https://api.pinterest.com/v5/oauth/token";

        $response = Curl::getContents( $token_url, 'POST', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->authData->refreshToken,
        ], [
            'Authorization' => 'Basic ' . base64_encode( urlencode( $this->authData->appId ) . ':' . urlencode( $this->authData->appSecret ) ),
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ], $this->proxy, true );

        $params = json_decode( $response, true );

        if ( isset( $params['message'] ) )
            throw new $this->authException( $params['message'] );

		$this->authData->accessToken = $params['access_token'];
		$this->authData->accessTokenExpiresOn = Date::dateTimeSQL( Date::epoch() + (int) $params[ 'expires_in' ] );
	}

	public function getMyInfo ()
	{
		$me = self::apiRequest( 'user_account', 'GET', [] )['data'];

		if ( ! isset( $me[ 'username' ] )  )
			throw new $this->authException( $me['message'] ?? 'Error' );

		return $me;
	}

	public function getMyBoards () : array
    {
		$bookmark = null;
		$boards   = [];

		do
		{
			$send_data = [ 'page_size' => 250 ];

			if ( ! empty( $bookmark ) )
			{
				$send_data[ 'bookmark' ] = $bookmark;
			}

			$page = $this->apiRequest( 'boards', 'GET', $send_data )['data'];

			if ( ! empty( $page[ 'items' ] ) )
			{
				foreach ( $page[ 'items' ] as $item )
				{
					$board = [
						'id'    => $item[ 'id' ],
						'name'  => $item[ 'name' ],
						'photo' => $item[ 'media' ][ 'image_cover_url' ] ?? '',
					];

					$boards[] = $board;
				}
				$bookmark = empty( $page[ 'bookmark' ] ) ? null : $page[ 'bookmark' ];
			}
			else
			{
				break;
			}
		} while ( ! empty( $bookmark ) );

		return $boards;
	}

	public static function getAuthURL ( $appId, $callbackUrl, $state ) : string
	{
		$appId = urlencode( $appId );
		$callbackUrl = urlencode( $callbackUrl );

		return "https://www.pinterest.com/oauth/?client_id={$appId}&redirect_uri=$callbackUrl&response_type=code&scope=boards:read,boards:write,pins:read,pins:write,user_accounts:read&state=" . urlencode( $state );
	}

}