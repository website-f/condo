<?php

namespace FSPoster\App\Providers\Core;

class ServiceLifetime
{
    public const SINGLETON = 'singleton';
    public const SCOPED = 'scoped';
    public const TRANSIENT = 'transient';
}
