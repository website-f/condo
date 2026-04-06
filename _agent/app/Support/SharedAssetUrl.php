<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class SharedAssetUrl
{
    public static function listing(?string $username, string|int|null $propertyId, ?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        if (self::shouldUseLocalStorageFallback($path) && self::existsOnPublicDisk($path)) {
            return self::storage($path);
        }

        if (self::isStoragePath($path)) {
            return self::storage($path);
        }

        $filename = trim((string) basename($path));

        if ($filename === '' || $username === null || $username === '' || $propertyId === null || $propertyId === '') {
            return null;
        }

        return rtrim((string) config('services.shared_assets.listing_base_url'), '/')
            . '/'
            . rawurlencode($username)
            . '/'
            . rawurlencode((string) $propertyId)
            . '_'
            . rawurlencode($filename);
    }

    public static function profile(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        return rtrim((string) config('services.shared_assets.profile_base_url'), '/')
            . '/'
            . rawurlencode((string) basename($path));
    }

    public static function storage(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null) {
            return null;
        }

        if (self::isAbsolute($path)) {
            return $path;
        }

        $relativePath = self::publicStoragePath($path);

        if ($relativePath === null) {
            return null;
        }

        return asset('storage/' . $relativePath);
    }

    public static function publicStoragePath(?string $path): ?string
    {
        $path = self::normalize($path);

        if ($path === null || self::isAbsolute($path)) {
            return null;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return $path === '' ? null : $path;
    }

    protected static function normalize(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = trim($path);

        return $path === '' ? null : $path;
    }

    protected static function isAbsolute(string $path): bool
    {
        return preg_match('#^(https?:)?//#i', $path) === 1;
    }

    protected static function isStoragePath(string $path): bool
    {
        $path = ltrim($path, '/');

        return str_starts_with($path, 'storage/')
            || str_starts_with($path, 'listings/');
    }

    protected static function shouldUseLocalStorageFallback(string $path): bool
    {
        $path = ltrim($path, '/');

        return str_starts_with($path, 'Database/Images/')
            || self::isStoragePath($path);
    }

    protected static function existsOnPublicDisk(string $path): bool
    {
        $relativePath = self::publicStoragePath($path);

        return $relativePath !== null && Storage::disk('public')->exists($relativePath);
    }
}
