<?php

namespace FSPoster\App\SocialNetworks\GoogleBusinessProfile\Api;

use Exception;
use FSPoster\App\Providers\Helpers\Date;
use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Exception\BadResponseException;
use FSPoster\GuzzleHttp\Exception\GuzzleException;
use Throwable;

class Api
{

	public AuthData $authData;
	public ?string  $proxy = null;
	public ?Client  $client = null;

	public string $authException = \Exception::class;
	public string $postException = \Exception::class;

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy = $proxy;
		$this->client = null;

		return $this;
	}

	public function getClient (): Client
	{
		if ( is_null( $this->client ) )
		{
			$this->client = new Client( [
				'proxy'  => empty( $this->proxy ) ? null : $this->proxy,
				'verify' => false,
			] );
		}

		return $this->client;
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
     * @throws Exception
     * @throws Throwable
     */
    public function sendPost (PostingData $postingData ) : string
	{
        $post = [];
        $post['summary'] = $postingData->message;
        $post['topicType'] = 'STANDARD';

        if ( ! empty( $postingData->uploadMedia ) )
        {
	        $post[ 'media' ][] = [
                'mediaFormat' => $postingData->uploadMedia[0]['type'] === 'video' ? 'VIDEO' : 'PHOTO',
                'sourceUrl'   => $postingData->uploadMedia[0]['url'],
            ];
        }

        if( ! empty( $postingData->link ) )
        {
            $post['callToAction'] = [
                'actionType' => $postingData->linkType
            ];

			if( $postingData->linkType !== 'CALL' ) {
				$post['callToAction']['url'] = $postingData->link;
			}
        }

        $posted = $this->apiRequest(
                'POST',
                'https://mybusiness.googleapis.com/v4/' . $postingData->accountId . '/' . $postingData->locationId . '/localPosts',
                [],
                json_encode($post)
            );

        if ( isset( $posted[ 'status' ] ) && $posted[ 'status' ] === 'error' )
	        throw new $this->postException( $posted['error_msg'] );

        if ( isset( $posted[ 'state' ] ) && $posted[ 'state' ] === 'REJECTED' )
	        throw new $this->postException( fsp__( 'Error! The post rejected by Google Business Profile' ) );

        if( empty($posted[ 'searchUrl' ]) )
			throw new $this->postException( fsp__( 'You need to verify your Google Business location to share posts.' ) );

        $parsed_link = parse_url( $posted[ 'searchUrl' ] );
        parse_str( $parsed_link[ 'query' ], $params );

        return $params[ 'lpsid' ] . '&id=' . $params[ 'id' ];
	}

    public function apiRequest(string $HTTPMethod, string $url, array $data = [], string $body = '', int $maxAttempts = 5): array
    {
        $HTTPMethod = strtoupper($HTTPMethod) === 'GET' ? 'GET' : 'POST';
        $attempt    = 0;

        $options = [];

        if (!empty($body)) {
            $options['body'] = $body;
        }

        if (!empty($data)) {
            $options['query'] = $data;
        }
        if (!empty($this->authData->accessToken)) {
            $options['headers'] = [
                'Connection'    => 'Keep-Alive',
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->authData->accessToken,
            ];
        }

        while (true) {
            try {
                $response = $this->getClient()->request($HTTPMethod, $url, $options)->getBody()->getContents();
            } catch (BadResponseException $e) {
                $statusCode = $e->getResponse()->getStatusCode();

                if (in_array($statusCode, [401, 403], true)) {
                    throw new $this->authException($e->getMessage());
                }

                $isRetryable = $statusCode === 429 || $statusCode >= 500;

                if ($isRetryable && ++$attempt < $maxAttempts) {
                    sleep($statusCode === 429 ? 60 : 2 ** $attempt);
                    continue;
                }

                throw new $this->postException($e->getMessage());
            } catch (GuzzleException $e) {
                if (++$attempt < $maxAttempts) {
                    sleep(2 ** $attempt);
                    continue;
                }
                throw new $this->postException($e->getMessage());
            }

            $decoded = json_decode($response, true);

            if (!is_array($decoded)) {
                throw new $this->postException($response);
            }

            if (isset($decoded['error'])) {
                $error = $decoded['error'];

                if (isset($error['status']) && $error['status'] === 'PERMISSION_DENIED') {
                    throw new $this->postException(fsp__('You need to verify your locations to share posts on it'));
                }

                $msg = $error['message']
                    ?? $decoded['error_description']
                    ?? 'Error';

                if (isset($error['details'][0]['errorDetails'][0]['message'])) {
                    $msg .= sprintf(' (%s)', $error['details'][0]['errorDetails'][0]['message']);
                }

                throw new $this->postException($msg);
            }

            return $decoded;
        }
    }

    public function fetchAccessToken ( string $code, string $callbackUrl ) : Api
    {
	    $options = [
		    'query' => [
			    'client_id'     => $this->authData->appClientId,
			    'client_secret' => $this->authData->appClientSecret,
			    'code'          => $code,
			    'grant_type'    => 'authorization_code',
			    'redirect_uri'  => $callbackUrl,
		    ],
	    ];

		try
		{
            $tokenInfo = $this->getClient()->post( 'https://oauth2.googleapis.com/token', $options )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
            throw new $this->authException( $e->getMessage() );
		}

	    $tokenInfo = json_decode( $tokenInfo, true );

	    if ( ! ( isset( $tokenInfo[ 'access_token' ] ) && isset( $tokenInfo[ 'refresh_token' ] ) ) )
		    throw new $this->authException( fsp__( 'Failed to get access token' ) );

		$this->authData->accessToken = $tokenInfo[ 'access_token' ];
		$this->authData->refreshToken = $tokenInfo[ 'refresh_token' ];
		$this->authData->accessTokenExpiresOn = Date::dateTimeSQL( 'now', '+55 minutes' );

        return $this;
	}

	public function getMyAccounts()
	{
		$accounts = $this->apiRequest( 'GET', 'https://mybusinessaccountmanagement.googleapis.com/v1/accounts' );

		return $accounts[ 'accounts' ] ?? [];
	}

    public function getLocations( string $accountId )
    {
		$allLocations = [];

        do
        {
            $queryData = [
                'readMask' => 'title,name',
                'pageSize' => 100,
            ];

            if ( ! empty( $nextPages ) )
                $queryData[ 'pageToken' ] = $nextPages;

            $response = $this->apiRequest( 'GET', 'https://mybusinessbusinessinformation.googleapis.com/v1/' . $accountId . '/locations', $queryData );

            $locations = $response[ 'locations' ] ?? [];
            $nextPages = $response[ 'nextPageToken' ] ?? false;

			$allLocations = array_merge( $allLocations, $locations );
        } while ( ! empty( $nextPages ) );

        return $allLocations;
    }

    private function refreshAccessTokenIfNeed()
    {
	    if (  ! empty( $this->authData->accessTokenExpiresOn ) && ( Date::epoch() + 30 ) > Date::epoch( $this->authData->accessTokenExpiresOn ) )
	    {
		    $this->refreshAccessToken();
	    }
    }

    private function refreshAccessToken () : void
	{
        $options = [
            'query' => [
                'client_id'     => $this->authData->appClientId,
                'client_secret' => $this->authData->appClientSecret,
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->authData->refreshToken,
            ],
        ];

        try
        {
            $refreshed_token = $this->getClient()->post( 'https://oauth2.googleapis.com/token', $options )->getBody()->getContents();
        }
        catch ( Exception $e )
        {
            throw new $this->authException( $e->getMessage() );
        }

		$refreshed_token = json_decode( $refreshed_token, true );

        if( empty( $refreshed_token[ 'access_token' ] ) )
            throw new $this->authException( fsp__( 'Failed to refresh access token' ) );

		$this->authData->accessToken = $refreshed_token[ 'access_token' ];
		$this->authData->accessTokenExpiresOn = Date::dateTimeSQL( 'now', '+55 minutes' );
	}

	public static function getAuthURL ( $appClientId, $callbackUrl ) : string
	{
		$authURL = 'https://accounts.google.com/o/oauth2/auth';

		$scopes = [
			'https://www.googleapis.com/auth/business.manage',
			'https://www.googleapis.com/auth/userinfo.profile',
			'email',
			'profile',
		];

		$params = [
			'response_type' => 'code',
			'access_type'   => 'offline',
			'client_id'     => $appClientId,
			'redirect_uri'  => $callbackUrl,
			'state'         => null,
			'scope'         => implode( ' ', $scopes ),
			'prompt'        => 'consent',
		];

		return $authURL . '?' . http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
	}

}
