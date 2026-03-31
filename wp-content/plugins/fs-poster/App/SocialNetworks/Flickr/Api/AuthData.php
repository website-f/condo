<?php

namespace FSPoster\App\SocialNetworks\Flickr\Api;

class AuthData
{
	public $consumerKey = '';
	public $consumerSecret = '';
	public $oauthToken = '';
	public $oauthTokenSecret = '';
	public $nsid = '';

	public function setFromArray ( array $data )
	{
		foreach ( $data as $key => $value )
		{
			if ( property_exists( $this, $key ) )
				$this->$key = $value;
		}
	}
}
