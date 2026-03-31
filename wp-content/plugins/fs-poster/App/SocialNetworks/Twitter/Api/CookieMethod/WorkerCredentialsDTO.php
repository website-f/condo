<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

class WorkerCredentialsDTO
{
    /** @var string */
    public $licenseCode;

    /** @var string */
    public $domain;

    public function __construct(string $licenseCode, string $domain)
    {
        $this->licenseCode = $licenseCode;
        $this->domain = $domain;
    }
}
