<?php

namespace FSPoster\App\SocialNetworks\Bluesky\Api\Helpers;

class Helper
{
    public static function isJWTExpired(string $jwt, int $offset = 0): bool
    {
        $payload = json_decode(base64_decode(explode(".", $jwt)[1]), true);

        return $payload['exp'] < (time() + $offset);
    }
}