<?php

namespace FSPoster\App\Providers\Exceptions;

use RuntimeException;

class CannotResolveParameterException extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(fsp__("Cannot resolve parameter $name without type hint"));
    }
}