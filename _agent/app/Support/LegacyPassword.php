<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use RuntimeException;

class LegacyPassword
{
    public static function check(string $plain, ?string $stored): bool
    {
        if (! is_string($stored) || $stored === '') {
            return false;
        }

        try {
            if (Hash::check($plain, $stored)) {
                return true;
            }
        } catch (RuntimeException) {
            // Legacy records may still store plain text or non-bcrypt hashes.
        }

        if (self::isPhpPasswordHash($stored)) {
            return password_verify($plain, $stored);
        }

        if (preg_match('/^[a-f0-9]{32}$/i', $stored) === 1) {
            return hash_equals(strtolower($stored), md5($plain));
        }

        if (preg_match('/^[a-f0-9]{40}$/i', $stored) === 1) {
            return hash_equals(strtolower($stored), sha1($plain));
        }

        return hash_equals($stored, $plain);
    }

    protected static function isPhpPasswordHash(string $stored): bool
    {
        return (password_get_info($stored)['algoName'] ?? 'unknown') !== 'unknown';
    }
}
