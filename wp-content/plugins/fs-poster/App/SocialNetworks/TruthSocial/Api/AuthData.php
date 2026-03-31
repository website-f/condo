<?php

namespace FSPoster\App\SocialNetworks\TruthSocial\Api;

class AuthData
{

	public string $server = 'https://truthsocial.com';
    public string $accessToken;


	public function setFromArray ( array $data ): void
    {
		foreach ( $data AS $key => $value )
		{
			if( property_exists( $this, $key ) )
				$this->$key = $value;
		}
	}

}
