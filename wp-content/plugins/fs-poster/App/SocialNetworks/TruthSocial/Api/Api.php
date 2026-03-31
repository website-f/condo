<?php

namespace FSPoster\App\SocialNetworks\TruthSocial\Api;

use Exception;
use FSPoster\App\Providers\Helpers\Helper;

class Api
{

	public const SERVER = 'https://truthsocial.com';

	private const USER_AGENT = 'TruthSocial/476 CFNetwork/3860.400.51 Darwin/25.3.0';

	public AuthData $authData;
	public ?string  $proxy = null;

	public string $authException = \Exception::class;
	public string $postException = \Exception::class;

	private ?CloudflareWorker $worker = null;

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

	public function initWorker ( string $licenseCode, string $domain ): self
	{
		$this->worker = ( new CloudflareWorker( $licenseCode, $domain ) )
			->setProxy( $this->proxy );

		return $this;
	}

    public function sendPost ( PostingData $postingData ) : string
    {
		$parameters = [];

		if ( ! empty( $postingData->message ) )
			$parameters[ 'status' ] = $postingData->message;

		if ( ! empty( $postingData->link ) )
		{
			$nl = empty( $parameters[ 'status' ] ) ? '' : "\n";
			$parameters[ 'status' ] = ( $parameters[ 'status' ] ?? '' ) . $nl . $postingData->link . "\n";
		}

		if ( ! empty( $postingData->uploadMedia ) )
			$parameters[ 'media_ids' ] = $this->uploadMedia( $postingData->uploadMedia );

		$post = $this->apiRequest( 'POST', 'api/v1/statuses', [
			'Content-Type' => 'application/json',
		], json_encode( $parameters ) );

		if ( ! isset( $post[ 'id' ] ) )
			throw new $this->postException( fsp__( 'Unexpected response from Truth Social' ) );

        return (string) $post[ 'id' ];
	}

	private function uploadMedia ( $medias ) : array
    {
		$data = [];

		foreach ( $medias as $media )
		{
			if ( empty( $media[ 'path' ] ) || ! file_exists( $media[ 'path' ] ) )
				continue;

			$mimeType = Helper::mimeContentType( $media[ 'path' ] );
			$fileName = basename( $media[ 'path' ] );
			$fileContents = file_get_contents( $media[ 'path' ] );

			$boundary = '----FSPosterBoundary' . bin2hex( random_bytes( 16 ) );

			$body = '--' . $boundary . "\r\n";
			$body .= 'Content-Disposition: form-data; name="file"; filename="' . $fileName . '"' . "\r\n";
			$body .= 'Content-Type: ' . $mimeType . "\r\n\r\n";
			$body .= $fileContents . "\r\n";
			$body .= '--' . $boundary . '--' . "\r\n";

			$response = $this->apiRequest( 'POST', 'api/v1/media', [
				'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
			], $body );

			if ( ! isset( $response[ 'id' ] ) )
				throw new $this->postException( fsp__( 'Unexpected response from Truth Social' ) );

			$data[] = $response[ 'id' ];
		}

		return $data;
	}

    /**
     * @throws \JsonException
     */
    private function apiRequest (string $method, string $endpoint, array $extraHeaders = [], string $body = '', ?string $exceptionClass = null ) : array
    {
		$exceptionClass = $exceptionClass ?? $this->postException;

		$endpoint = trim( $endpoint, '/' );
		$url = self::SERVER . '/' . $endpoint;

		$headers = array_merge( [
			'User-Agent'    => self::USER_AGENT,
			'Accept'        => '*/*',
		], $extraHeaders );

		if ( ! empty( $this->authData->accessToken ) )
			$headers[ 'Authorization' ] = 'Bearer ' . $this->authData->accessToken;

		if ( $this->worker === null )
			throw new $exceptionClass( fsp__( 'Cloudflare Worker is not configured' ) );

		$workerResult = $this->worker->sendRequest( $url, $method, $headers, $body );
		$workerStatus = $workerResult[ 'status' ] ?? 500;
		$responseBody = $workerResult[ 'body' ] ?? '';

		try
		{
			$response = json_decode( $responseBody, true, 512, JSON_THROW_ON_ERROR );
		}
		catch ( \JsonException $e )
		{
			$response = null;
		}

		if ( $workerStatus >= 400 )
		{
			$errorMessage = '';

			if ( is_array( $response ) )
				$errorMessage = $response[ 'error_description' ] ?? $response[ 'error' ] ?? '';

			$errorMessage = $errorMessage ?: mb_substr( $responseBody, 0, 500 );

			if ( $workerStatus === 401 || $workerStatus === 403 )
				throw new $this->authException( $errorMessage );

			throw new $exceptionClass( $errorMessage );
		}

		if ( isset( $response[ 'error' ] ) )
			throw new $exceptionClass( $response[ 'error_description' ] ?? $response[ 'error' ] );

		if ( ! is_array( $response ) )
			throw new $exceptionClass( fsp__( 'Unexpected response from Truth Social' ) );

		return $response;
	}

	public function getMyInfo (): array
    {
		$response = $this->apiRequest( 'GET', 'api/v1/accounts/verify_credentials', [], '', $this->authException );

		if ( ! isset( $response[ 'id' ] ) )
			throw new $this->authException( 'Error' );

		return $response;
	}

}
