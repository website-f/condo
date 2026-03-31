<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

class XPFFHeaderGenerator
{
    private string $baseKey = '0e6be1f1e21ffc33590b888fd4dc81b19713e570e805d4e5df80a493c9571a05';

    private function deriveXpffKey(string $guestId): string
    {
        $combined = $this->baseKey . $guestId;
        return hash('sha256', $combined, true); // binary output
    }

    /**
     * @throws \JsonException
     */
    public function generateXPFF(string $userAgent, string $guestId): string
    {
        $plaintext = [
            "navigator_properties" => [
                "hasBeenActive" => "true",
                "userAgent" => $userAgent,
                "webdriver" => "false"
            ],
            "created_at" => time()
        ];
        $plaintext = json_encode($plaintext, JSON_THROW_ON_ERROR);

        $key = $this->deriveXpffKey($guestId);
        $nonce = random_bytes(12);

        $ciphertext = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            '',
            16 // tag length
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }

        return bin2hex($nonce . $ciphertext . $tag);
    }

    public function decodeXpff(string $hexString, string $guestId): string
    {
        $key = $this->deriveXpffKey($guestId);
        $raw = hex2bin($hexString);

        if ($raw === false) {
            throw new \InvalidArgumentException('Invalid hex string');
        }

        $nonce = substr($raw, 0, 12);
        $tag = substr($raw, -16);
        $ciphertext = substr($raw, 12, -16);

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException('Decryption failed or authentication tag mismatch');
        }

        return $plaintext;
    }

}