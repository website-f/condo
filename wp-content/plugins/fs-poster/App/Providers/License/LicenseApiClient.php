<?php

namespace FSPoster\App\Providers\License;

use FSPoster\GuzzleHttp\Client;
use FSPoster\GuzzleHttp\Exception\GuzzleException;

class LicenseApiClient
{

	private const API_URL = 'https://api.fs-code.com/v3/fs-poster/product/';
	private ?string $proxy = null;

    public LicenseApiClientContextDto $context;

    public function __construct(LicenseApiClientContextDto $context)
    {
        $this->context = $context;
    }

    // context dynamic deyishir deye client-i cachelemeye chalishma.
    private function getClient(): Client
    {
        return new Client([
            'verify' => false,
            'proxy' => $this->proxy ?: null,
            'headers' => [
                'X-License-Code'      => $this->context->licenseCode,
                'X-Website'           => $this->context->website,
                'X-Product-Version'   => $this->context->productVersion,
                'X-PHP-Version'       => $this->context->phpVersion,
                'X-Wordpress-Version' => $this->context->wordpressVersion,
                'Content-type'        => 'application/json',
                'Accept'              => 'application/json',
            ],
        ]);
    }

    public function request ($endpoint, $method = 'GET', $data = [])
	{
		$url = static::API_URL . $endpoint;

		$options = [];

		if( $method === 'POST' && ! empty( $data ) ) {
            $options['form_params'] = $data;
        }
		else if( $method === 'GET' && ! empty( $data ) ) {
            $options['query'] = $data;
        }

		try
		{
			$response = $this->getClient()->request( $method, $url, $options );
			$response = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
		}
		catch ( \Exception|GuzzleException $e )
		{
			$response = [];
		}

        return $response;
	}

	public function setProxy ( $proxy ): LicenseApiClient
    {
		$this->proxy = $proxy;

		return $this;
	}

}