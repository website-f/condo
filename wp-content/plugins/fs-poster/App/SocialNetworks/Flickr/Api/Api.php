<?php

namespace FSPoster\App\SocialNetworks\Flickr\Api;

use Exception;
use FSPoster\App\Providers\Helpers\Helper;
use FSPoster\GuzzleHttp\Client;

class Api
{
	public AuthData $authData;
	public ?string  $proxy = null;
	public string   $authException = \Exception::class;
	public string   $postException = \Exception::class;

	private ?Client $client = null;

	private const API_URL    = 'https://api.flickr.com/services/rest/';
	private const UPLOAD_URL = 'https://up.flickr.com/services/upload/';

	private const REQUEST_TOKEN_URL = 'https://www.flickr.com/services/oauth/request_token';
	private const AUTHORIZE_URL     = 'https://www.flickr.com/services/oauth/authorize';
	private const ACCESS_TOKEN_URL  = 'https://www.flickr.com/services/oauth/access_token';

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy  = $proxy;
		$this->client = null;

		return $this;
	}

	private function getClient (): Client
	{
		if ( $this->client === null )
		{
			$options = [ 'verify' => false ];

			if ( ! empty( $this->proxy ) )
				$options['proxy'] = $this->proxy;

			$this->client = new Client( $options );
		}

		return $this->client;
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

	/**
	 * Upload a photo to Flickr and optionally add it to an album.
	 *
	 * @param PostingData $postingData
	 *
	 * @return array ['id' => string, 'url' => string]
	 */
	public function sendPost ( PostingData $postingData ): array
	{
		if ( empty( $postingData->uploadMedia ) )
			throw new $this->postException( fsp__( 'An image or video is required to post on Flickr' ) );

		$mediaPath = $postingData->uploadMedia[0]['path'] ?? '';

		if ( empty( $mediaPath ) || ! file_exists( $mediaPath ) )
			throw new $this->postException( fsp__( 'Media file not found' ) );

		$photoId = $this->uploadPhoto( $mediaPath, $postingData );

		if ( ! empty( $postingData->albumId ) )
		{
			$this->addPhotoToAlbum( $photoId, $postingData->albumId );
		}

		if ( ! empty( $postingData->firstComment ) )
		{
			$this->addComment( $photoId, $postingData->firstComment );
		}

		$url = 'https://www.flickr.com/photos/' . $this->authData->nsid . '/' . $photoId . '/';

		return [
			'id'  => $photoId,
			'url' => $url,
		];
	}

	/**
	 * Upload a photo via the Flickr Upload API.
	 * Note: The 'photo' parameter must be excluded from the OAuth signature.
	 *
	 * @see https://www.flickr.com/services/api/upload.api.html
	 */
	private function uploadPhoto ( string $imagePath, PostingData $postingData ): string
	{
		$params = [
			'title'       => $postingData->title,
			'description' => $postingData->description,
			'tags'        => $postingData->tags,
			'is_public'   => (string)$postingData->isPublic,
			'is_friend'   => (string)$postingData->isFriend,
			'is_family'   => (string)$postingData->isFamily,
		];

		$oauthParams = $this->getOAuthParams();
		$allSignableParams = array_merge( $oauthParams, $params );

		$oauthParams['oauth_signature'] = $this->buildSignature( 'POST', self::UPLOAD_URL, $allSignableParams );

		$multipart = [];

		foreach ( array_merge( $oauthParams, $params ) as $key => $value )
		{
			$multipart[] = [
				'name'     => $key,
				'contents' => $value,
			];
		}

		$mimeType = Helper::mimeContentType( $imagePath );

		$multipart[] = [
			'name'     => 'photo',
			'contents' => fopen( $imagePath, 'r' ),
			'filename' => basename( $imagePath ),
			'headers'  => [ 'Content-Type' => $mimeType ],
		];

		$options = [
			'multipart' => $multipart,
		];

		try
		{
			$response = $this->getClient()->post( self::UPLOAD_URL, $options )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) && ! empty( $e->getResponse() ) )
				throw new $this->postException( $e->getResponse()->getBody()->getContents() );

			throw new $this->postException( $e->getMessage() );
		}

		$photoId = $this->parseUploadResponse( $response );

		if ( empty( $photoId ) )
			throw new $this->postException( fsp__( 'Failed to upload photo to Flickr' ) );

		return $photoId;
	}

	/**
	 * Parse the XML response from the upload endpoint.
	 */
	private function parseUploadResponse ( string $xml ): string
	{
		libxml_use_internal_errors( true );
		$doc = simplexml_load_string( $xml );

		if ( $doc === false )
			throw new $this->postException( fsp__( 'Invalid response from Flickr upload API' ) );

		if ( (string)$doc['stat'] !== 'ok' )
		{
			$errorMsg = isset( $doc->err ) ? (string)$doc->err['msg'] : fsp__( 'Unknown upload error' );
			throw new $this->postException( $errorMsg );
		}

		return (string)$doc->photoid;
	}

	/**
	 * Call a Flickr REST API method.
	 */
	public function apiRequest ( string $method, array $params = [], string $httpMethod = 'GET' ): array
	{
		$params['method']         = $method;
		$params['format']         = 'json';
		$params['nojsoncallback'] = '1';

		$oauthParams = $this->getOAuthParams();
		$allParams = array_merge( $oauthParams, $params );

		$oauthParams['oauth_signature'] = $this->buildSignature( $httpMethod, self::API_URL, $allParams );

		$allParams = array_merge( $oauthParams, $params );

		try
		{
			if ( $httpMethod === 'POST' )
			{
				$response = $this->getClient()->post( self::API_URL, [ 'form_params' => $allParams ] )->getBody()->getContents();
			}
			else
			{
				$response = $this->getClient()->get( self::API_URL, [ 'query' => $allParams ] )->getBody()->getContents();
			}
		}
		catch ( Exception $e )
		{
			if ( method_exists( $e, 'getResponse' ) && ! empty( $e->getResponse() ) )
				throw new $this->postException( $e->getResponse()->getBody()->getContents() );

			throw new $this->postException( $e->getMessage() );
		}

		$data = json_decode( $response, true );

		if ( ! is_array( $data ) )
			throw new $this->postException( fsp__( 'Invalid response from Flickr API' ) );

		if ( isset( $data['stat'] ) && $data['stat'] === 'fail' )
		{
			$errorMsg = $data['message'] ?? fsp__( 'Unknown Flickr API error' );
			throw new $this->postException( $errorMsg );
		}

		return $data;
	}

	/**
	 * Get user profile info.
	 *
	 * @see https://www.flickr.com/services/api/flickr.test.login.html
	 * @see https://www.flickr.com/services/api/flickr.people.getInfo.html
	 */
	public function getMyInfo (): array
	{
		$loginResult = $this->apiRequest( 'flickr.test.login' );

		if ( empty( $loginResult['user']['id'] ) )
			throw new $this->authException( fsp__( 'Failed to verify Flickr credentials' ) );

		$nsid = $loginResult['user']['id'];

		$personResult = $this->apiRequest( 'flickr.people.getInfo', [ 'user_id' => $nsid ] );

		if ( empty( $personResult['person'] ) )
			throw new $this->authException( fsp__( 'Failed to fetch Flickr user info' ) );

		$person = $personResult['person'];

		$iconServer = $person['iconserver'] ?? '0';
		$iconFarm   = $person['iconfarm'] ?? '0';

		if ( (int)$iconServer > 0 )
		{
			$buddyIcon = "https://farm{$iconFarm}.staticflickr.com/{$iconServer}/buddyicons/{$nsid}.jpg";
		}
		else
		{
			$buddyIcon = 'https://www.flickr.com/images/buddyicon.gif';
		}

		return [
			'id'       => $nsid,
			'username' => $person['username']['_content'] ?? '',
			'realname' => $person['realname']['_content'] ?? '',
			'picture'  => $buddyIcon,
			'url'      => $person['photosurl']['_content'] ?? ( 'https://www.flickr.com/photos/' . $nsid . '/' ),
		];
	}

	/**
	 * Get user's albums (photosets).
	 *
	 * @see https://www.flickr.com/services/api/flickr.photosets.getList.html
	 */
	public function getAlbums (): array
	{
		$result = $this->apiRequest( 'flickr.photosets.getList', [
			'user_id'  => $this->authData->nsid,
			'per_page' => '500',
		] );

		$albums = [];

		if ( ! empty( $result['photosets']['photoset'] ) )
		{
			foreach ( $result['photosets']['photoset'] as $photoset )
			{
				$albums[] = [
					'id'   => $photoset['id'],
					'name' => $photoset['title']['_content'] ?? '',
				];
			}
		}

		return $albums;
	}

	/**
	 * Add a photo to an existing album.
	 *
	 * @see https://www.flickr.com/services/api/flickr.photosets.addPhoto.html
	 */
	public function addPhotoToAlbum ( string $photoId, string $albumId ): void
	{
		$this->apiRequest( 'flickr.photosets.addPhoto', [
			'photoset_id' => $albumId,
			'photo_id'    => $photoId,
		], 'POST' );
	}

	/**
	 * Add a comment to a photo.
	 *
	 * @see https://www.flickr.com/services/api/flickr.photos.comments.addComment.html
	 */
	public function addComment ( string $photoId, string $commentText ): void
	{
		$this->apiRequest( 'flickr.photos.comments.addComment', [
			'photo_id'     => $photoId,
			'comment_text' => $commentText,
		], 'POST' );
	}

	/**
	 * OAuth 1.0a: Get a request token and return the authorization URL.
	 *
	 * @see https://www.flickr.com/services/api/auth.oauth.html
	 */
	public static function getAuthURL ( string $consumerKey, string $consumerSecret, string $callbackUrl, ?string $proxy = null ): array
	{
		$oauthParams = [
			'oauth_callback'         => $callbackUrl,
			'oauth_consumer_key'     => $consumerKey,
			'oauth_nonce'            => self::generateNonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (string)time(),
			'oauth_version'          => '1.0',
		];

		$oauthParams['oauth_signature'] = self::buildSignatureStatic(
			'GET',
			self::REQUEST_TOKEN_URL,
			$oauthParams,
			$consumerSecret,
			''
		);

		$url = self::REQUEST_TOKEN_URL . '?' . http_build_query( $oauthParams, '', '&', PHP_QUERY_RFC3986 );

		$clientOptions = [ 'verify' => false ];

		if ( ! empty( $proxy ) )
			$clientOptions['proxy'] = $proxy;

		$client = new Client( $clientOptions );

		try
		{
			$response = $client->get( $url )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			throw new Exception( fsp__( 'Failed to get Flickr request token: ' ) . $e->getMessage() );
		}

		parse_str( $response, $parsed );

		if ( empty( $parsed['oauth_token'] ) || empty( $parsed['oauth_token_secret'] ) )
			throw new Exception( fsp__( 'Invalid response from Flickr request token endpoint' ) );

		$authorizeUrl = self::AUTHORIZE_URL . '?oauth_token=' . urlencode( $parsed['oauth_token'] ) . '&perms=write';

		return [
			'url'                => $authorizeUrl,
			'oauth_token'        => $parsed['oauth_token'],
			'oauth_token_secret' => $parsed['oauth_token_secret'],
		];
	}

	/**
	 * OAuth 1.0a: Exchange request token + verifier for access token.
	 *
	 * @see https://www.flickr.com/services/api/auth.oauth.html
	 */
	public function fetchAccessToken ( string $oauthToken, string $oauthTokenSecret, string $oauthVerifier ): Api
	{
		$oauthParams = [
			'oauth_consumer_key'     => $this->authData->consumerKey,
			'oauth_nonce'            => self::generateNonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (string)time(),
			'oauth_token'            => $oauthToken,
			'oauth_verifier'         => $oauthVerifier,
			'oauth_version'          => '1.0',
		];

		$oauthParams['oauth_signature'] = self::buildSignatureStatic(
			'GET',
			self::ACCESS_TOKEN_URL,
			$oauthParams,
			$this->authData->consumerSecret,
			$oauthTokenSecret
		);

		$url = self::ACCESS_TOKEN_URL . '?' . http_build_query( $oauthParams, '', '&', PHP_QUERY_RFC3986 );

		try
		{
			$response = $this->getClient()->get( $url )->getBody()->getContents();
		}
		catch ( Exception $e )
		{
			throw new $this->authException( fsp__( 'Failed to get Flickr access token: ' ) . $e->getMessage() );
		}

		parse_str( $response, $parsed );

		if ( empty( $parsed['oauth_token'] ) || empty( $parsed['oauth_token_secret'] ) )
			throw new $this->authException( fsp__( 'Failed to obtain Flickr access token' ) );

		$this->authData->oauthToken       = $parsed['oauth_token'];
		$this->authData->oauthTokenSecret = $parsed['oauth_token_secret'];
		$this->authData->nsid             = $parsed['user_nsid'] ?? '';

		return $this;
	}

	/**
	 * Generate OAuth base params for authenticated requests.
	 */
	private function getOAuthParams (): array
	{
		return [
			'oauth_consumer_key'     => $this->authData->consumerKey,
			'oauth_nonce'            => self::generateNonce(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp'        => (string)time(),
			'oauth_token'            => $this->authData->oauthToken,
			'oauth_version'          => '1.0',
		];
	}

	/**
	 * Build HMAC-SHA1 signature for an OAuth request.
	 */
	private function buildSignature ( string $method, string $url, array $params ): string
	{
		return self::buildSignatureStatic(
			$method,
			$url,
			$params,
			$this->authData->consumerSecret,
			$this->authData->oauthTokenSecret
		);
	}

	/**
	 * Static helper to build HMAC-SHA1 signature.
	 */
	private static function buildSignatureStatic ( string $method, string $url, array $params, string $consumerSecret, string $tokenSecret ): string
	{
		ksort( $params );

		$paramString  = http_build_query( $params, '', '&', PHP_QUERY_RFC3986 );
		$baseString   = strtoupper( $method ) . '&' . rawurlencode( $url ) . '&' . rawurlencode( $paramString );
		$signingKey   = rawurlencode( $consumerSecret ) . '&' . rawurlencode( $tokenSecret );

		return base64_encode( hash_hmac( 'sha1', $baseString, $signingKey, true ) );
	}

	private static function generateNonce (): string
	{
		return md5( microtime( true ) . wp_rand() );
	}
}
