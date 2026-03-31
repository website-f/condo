<?php

namespace FSPoster\App\SocialNetworks\WordPress\Api\RestApi;

class AuthData {
    public string $siteUrl;
    public string $applicationName;
    public string $applicationPassword;

    public function setFromArray(array $data): void
    {
        foreach ( $data AS $key => $value )
        {
            if( property_exists( $this, $key ) ) {
                $this->$key = $value;
            }
        }
    }
}
