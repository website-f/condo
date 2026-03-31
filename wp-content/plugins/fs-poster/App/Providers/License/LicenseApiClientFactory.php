<?php

namespace FSPoster\App\Providers\License;

use FSPoster\App\Providers\Core\Settings;
use FSPoster\App\Providers\Helpers\PluginHelper;

final class LicenseApiClientFactory
{
    public function make(): LicenseApiClient
    {
        $dto = $this->getContext();

        return new LicenseApiClient($dto);
    }

    private function getContext(): LicenseApiClientContextDto
    {
        $license  = Settings::get( 'license_code', '', true );
        $website  = network_site_url();
        $version  = PluginHelper::getVersion();

        return new LicenseApiClientContextDto(
            $license,
            $website,
            $version,
            PHP_VERSION,
            get_bloginfo('version')
        );
    }
}