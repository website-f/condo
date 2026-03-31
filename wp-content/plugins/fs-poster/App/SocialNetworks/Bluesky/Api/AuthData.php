<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Api;

class AuthData
{
    public string $identifier;
    public string $appPassword;
    public string $accessJwt;
    public string $refreshJwt;
    public string $did;
    public string $serviceEndpoint;

    public function setFromArray ( array $data )
    {
        foreach ( $data AS $key => $value )
        {
            if( property_exists( $this, $key ) )
                $this->$key = $value;
        }
    }
}