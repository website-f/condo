<?php

namespace FSPoster\App\SocialNetworks\TruthSocial\Api;

use Exception;
use FSPoster\GuzzleHttp\Client;

class CloudflareWorker
{
	private const WORKER_URL = 'https://truthsocial-proxy.fs-poster.com';

	private string $licenseCode;
	private string $domain;
	private ?string $proxy = null;

	public function __construct ( string $licenseCode, string $domain )
	{
		$this->licenseCode = $licenseCode;
		$this->domain      = $domain;
	}

	public function setProxy ( ?string $proxy ): self
	{
		$this->proxy = $proxy;

		return $this;
	}

	public function sendRequest ( string $url, string $method, array $headers, string $body = '' ): array
	{
		try
		{
			$client = new Client( [
				'verify'      => false,
				'http_errors' => false,
				'timeout'     => 30,
				'proxy'       => $this->proxy ?: null,
			] );

			$workerPayload = [
				'url'      => $url,
				'method'   => $method,
				'headers'  => $headers,
				'body'     => base64_encode( $body ),
				'body_b64' => true,
			];

			$response = $client->post( self::WORKER_URL, [
				'headers' => [
					'Content-Type'  => 'application/json',
					'X-FSP-License' => $this->licenseCode,
					'X-FSP-Domain'  => $this->domain,
				],
				'body'    => json_encode( $workerPayload, JSON_THROW_ON_ERROR ),
			] );

			$result = json_decode( $response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR );
		}
		catch ( Exception $e )
		{
			return [
				'status' => 500,
				'body'   => json_encode( [ 'error' => fsp__( 'Failed to connect to proxy server' ) ] ),
			];
		}

		return [
			'status' => $result[ 'status' ] ?? 500,
			'body'   => $result[ 'body' ] ?? '',
		];
	}
}
