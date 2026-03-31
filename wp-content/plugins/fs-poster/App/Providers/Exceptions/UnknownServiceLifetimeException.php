<?php

namespace FSPoster\App\Providers\Exceptions;

use RuntimeException;

class UnknownServiceLifetimeException extends RuntimeException
{
    public function __construct(string $lifetime)
    {
        parent::__construct("Unknown service lifetime: $lifetime");
    }
}