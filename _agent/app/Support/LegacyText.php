<?php

namespace App\Support;

class LegacyText
{
    public static function decode(?string $value): ?string
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        if (! self::isEncoded($value)) {
            return $value;
        }

        preg_match_all('/u([0-9a-fA-F]{4})/', $value, $matches);

        if (empty($matches[1])) {
            return $value;
        }

        $decoded = '';

        foreach ($matches[1] as $hex) {
            $codepoint = hexdec($hex);
            $decoded .= function_exists('mb_chr')
                ? mb_chr($codepoint, 'UTF-8')
                : html_entity_decode('&#' . $codepoint . ';', ENT_QUOTES, 'UTF-8');
        }

        return $decoded;
    }

    public static function encode(?string $value): ?string
    {
        if (! is_string($value)) {
            return $value;
        }

        $value = trim($value);

        if ($value === '' || self::isEncoded($value)) {
            return $value;
        }

        $characters = preg_split('//u', $value, -1, PREG_SPLIT_NO_EMPTY);

        if ($characters === false) {
            return $value;
        }

        $encoded = '';

        foreach ($characters as $character) {
            $encoded .= sprintf('u%04x', mb_ord($character, 'UTF-8'));
        }

        return $encoded;
    }

    public static function isEncoded(?string $value): bool
    {
        return is_string($value) && preg_match('/^(?:u[0-9a-fA-F]{4})+$/', $value) === 1;
    }
}
