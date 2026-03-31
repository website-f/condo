<?php

namespace FSPoster\App\SocialNetworks\Twitter\Api\CookieMethod;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use RuntimeException;

class XClientTransactionGenerator
{
    private string $randomKeyword = 'obfiowerehiring';
    private int $randomNumber = 3;

    // Default indices (can be overridden by getIndices from ondemand file)
    private int $rowIndex = 2;
    private array $keyBytesIndices = [12, 14, 7];

    public function generate(
        string  $method,
        string  $path,
                $homePageResponse = null,
        ?string $key = null,
        ?string $animationKey = null,
        ?int    $timeNow = null
    ): string
    {
        // === time_now ===
        if ($timeNow === null) {
            $timeNow = (int)floor(
                ((microtime(true) * 1000) - (1682924400 * 1000)) / 1000
            );
        }

        // === time_now_bytes (4 bytes, little-endian) ===
        $timeNowBytes = [];
        for ($i = 0; $i < 4; $i++) {
            $timeNowBytes[] = ($timeNow >> ($i * 8)) & 0xFF;
        }

        // === key + key_bytes ===
        $key = $key ?? $this->key ?? $this->getKey($homePageResponse);
        $keyBytes = $this->getKeyBytes($key);

        // === animation_key ===
        $animationKey = $animationKey
            ?? $this->animationKey
            ?? $this->getAnimationKey($keyBytes, $homePageResponse);

        // === SHA-256 hash ===
        $hashInput =
            $method . '!' .
            $path . '!' .
            $timeNow .
            $this->randomKeyword .
            $animationKey;

        $hashBinary = hash('sha256', $hashInput, true);

        // Convert binary hash → byte array
        $hashBytes = array_values(unpack('C*', $hashBinary));

        // === random byte ===
        $randomNum = random_int(0, 255);

        // === bytes_arr ===
        $bytesArr = array_merge(
            $keyBytes,
            $timeNowBytes,
            array_slice($hashBytes, 0, 16),
            [$this->randomNumber]
        );

        // === XOR obfuscation ===
        $outBytes = [$randomNum];
        foreach ($bytesArr as $b) {
            $outBytes[] = $b ^ $randomNum;
        }

        // Convert byte array → binary string
        $outBinary = pack('C*', ...$outBytes);

        // === Base64 encode, strip padding ===
        return rtrim(base64_encode($outBinary), '=');
    }

    public function animate(array $frames, float $targetTime): string
    {
        // === from_color & to_color ===
        $fromColor = [
            (float)$frames[0],
            (float)$frames[1],
            (float)$frames[2],
            1.0
        ];

        $toColor = [
            (float)$frames[3],
            (float)$frames[4],
            (float)$frames[5],
            1.0
        ];

        // === rotation ===
        $fromRotation = [0.0];
        $toRotation = [
            $this->solve((float)$frames[6], 60.0, 360.0, true)
        ];

        // === remaining frames ===
        $frames = array_slice($frames, 7);

        // === cubic curves ===
        $curves = [];
        foreach ($frames as $counter => $item) {
            $curves[] = $this->solve(
                (float)$item,
                $this->isOdd($counter),
                1.0,
                false
            );
        }

        $cubic = new Cubic($curves);
        $val = $cubic->getValue($targetTime);

        // === color interpolation ===
        $color = $this->interpolate($fromColor, $toColor, $val);
        foreach ($color as &$value) {
            $value = max(0, min(255, $value));
        }
        unset($value);

        // === rotation interpolation ===
        $rotation = $this->interpolate($fromRotation, $toRotation, $val);
        $matrix = $this->convertRotationToMatrix($rotation[0]);

        // === build hex string array ===
        $strArr = [];
        for ($i = 0; $i < count($color) - 1; $i++) {
            $strArr[] = dechex((int)round($color[$i]));
        }

        foreach ($matrix as $value) {
            $rounded = round($value, 2);
            if ($rounded < 0) {
                $rounded = -$rounded;
            }

            $hexValue = $this->floatToHex($rounded);

            if ($hexValue === '') {
                $strArr[] = '0';
            } elseif ($hexValue[0] === '.') {
                $strArr[] = strtolower('0' . $hexValue);
            } else {
                $strArr[] = strtolower($hexValue);
            }
        }

        // === padding ===
        $strArr[] = '0';
        $strArr[] = '0';

        // === final animation key ===
        return preg_replace('/[.-]/', '', implode('', $strArr));
    }

    /**
     * @throws Exception
     */
    public function getKey(string $html): string
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        // Equivalent to: meta[name="twitter-site-verification"]
        $nodes = $xpath->query('//meta[@name="twitter-site-verification"]');

        if ($nodes->length === 0) {
            throw new \RuntimeException(
                "Couldn't get [twitter-site-verification] key from the page source"
            );
        }

        /** @var DOMElement $meta */
        $meta = $nodes->item(0);
        $content = $meta->getAttribute('content');

        if ($content === '') {
            throw new \RuntimeException(
                "[twitter-site-verification] meta tag has no content attribute"
            );
        }

        return $content;
    }

    public function getKeyBytes(string $key): array
    {
        $decoded = base64_decode($key, true);

        if ($decoded === false) {
            throw new Exception("Invalid base64 key");
        }

        // Convert binary string → array of integers (0–255)
        return array_values(unpack('C*', $decoded));
    }

    public function solve($value, $minVal, $maxVal, bool $rounding)
    {
        $result = $value * ($maxVal - $minVal) / 255 + $minVal;

        return $rounding ? floor($result) : round($result, 2);
    }

    public function isOdd(int $number): int
    {
        return $number % 2 !== 0 ? -1 : 0;
    }

    public function interpolate(array $fromList, array $toList, $f): array
    {
        if (count($fromList) !== count($toList)) {
            throw new \RuntimeException("Mismatched interpolation arguments");
        }

        $out = [];
        $fromListCount = count($fromList);
        for ($i = 0; $i < $fromListCount; $i++) {
            $out[] = $this->interpolateNum($fromList[$i], $toList[$i], $f);
        }

        return $out;
    }

    public function interpolateNum($fromVal, $toVal, $f)
    {
        if (is_numeric($fromVal) && is_numeric($toVal)) {
            return $fromVal * (1 - $f) + $toVal * $f;
        }

        if (is_bool($fromVal) && is_bool($toVal)) {
            return $f < 0.5 ? $fromVal : $toVal;
        }

        return null;
    }

    public function convertRotationToMatrix($rotation): array
    {
        $rad = deg2rad($rotation);

        return [cos($rad), -sin($rad), sin($rad), cos($rad)];
    }

    public function floatToHex($x): string
    {
        $result = [];
        $quotient = (int)$x;
        $fraction = $x - $quotient;

        while ($quotient > 0) {
            $quotient = (int)($x / 16);
            $remainder = (int)($x - ((float)$quotient * 16));

            if ($remainder > 9) {
                array_unshift($result, chr($remainder + 55));
            } else {
                array_unshift($result, (string)$remainder);
            }

            $x = (float)$quotient;
        }

        if ($fraction == 0) {
            return implode('', $result);
        }

        $result[] = '.';

        while ($fraction > 0) {
            $fraction *= 16;
            $integer = (int)$fraction;
            $fraction -= (float)$integer;

            if ($integer > 9) {
                $result[] = chr($integer + 55);
            } else {
                $result[] = (string)$integer;
            }
        }

        return implode('', $result);
    }

    public function getAnimationKey(array $keyBytes, string $homePageHtml): string
    {
        $totalTime = 4096;

        $rowIndex = $keyBytes[$this->rowIndex] % 16;

        // reduce: multiply all (keyBytes[index] % 16) values
        $frameTime = 1;
        foreach ($this->keyBytesIndices as $index) {
            $frameTime *= $keyBytes[$index] % 16;
        }

        $frameTime = $this->mathRound($frameTime / 10) * 10;

        $arr = $this->get2dArray($keyBytes, $homePageHtml);
        $frameRow = $arr[$rowIndex];

        $targetTime = (float)$frameTime / $totalTime;

        return $this->animate($frameRow, $targetTime);
    }

    public function getFrames(string $html): array
    {
        libxml_use_internal_errors(true);

        $dom = new DOMDocument();
        $dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        // Select elements with id starting with 'loading-x-anim'
        $nodes = $xpath->query("//*[starts-with(@id, 'loading-x-anim')]");

        $frames = [];
        foreach ($nodes as $node) {
            $frames[] = $node;
        }

        return $frames;
    }

    public function get2dArray(array $keyBytes, string $homePageHtml, ?array $frames = null): array
    {
        if ($frames === null) {
            $frames = $this->getFrames($homePageHtml);
        }

        // Get the frame based on keyBytes[5] % 4
        $frameIndex = $keyBytes[5] % 4;
        $frame = $frames[$frameIndex];

        // Navigate: frame -> first child (g/svg) -> children -> second child -> get "d" attribute
        // In Python: list(list(frames[key_bytes[5] % 4].children)[0].children)[1].get("d")[9:].split("C")
        $firstChild = $frame->firstChild;

        // Skip text nodes to get actual element
        while ($firstChild && $firstChild->nodeType !== XML_ELEMENT_NODE) {
            $firstChild = $firstChild->nextSibling;
        }

        // Get children of first child
        $children = [];
        foreach ($firstChild->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $children[] = $child;
            }
        }

        // Get second child (index 1)
        $secondChild = $children[1];

        // Get "d" attribute, skip first 9 chars, split by "C"
        $dAttr = $secondChild->getAttribute('d');
        $dValue = substr($dAttr, 9);
        $parts = explode('C', $dValue);

        // Convert each part to array of integers
        $result = [];
        foreach ($parts as $item) {
            // Replace non-digits with space, trim, split by space
            $cleaned = trim(preg_replace('/[^\d]+/', ' ', $item));
            $numbers = array_map('intval', explode(' ', $cleaned));
            $result[] = $numbers;
        }

        return $result;
    }

    public function mathRound($num)
    {
        // JavaScript-like rounding
        $x = floor($num);
        if (($num - $x) >= 0.5) {
            $x = ceil($num);
        }

        return $num < 0 ? -abs($x) : abs($x);
    }

    /**
     * @throws RuntimeException
     */
    public function getIndices(string $ondemandFileResponse): array
    {
        // Pattern matches: (e[2], 16) or (x[42], 16)
        $pattern = '/\(\w\[(\d{1,2})],\s*16\)/';
        preg_match_all($pattern, $ondemandFileResponse, $matches);

        if (empty($matches[1])) {
            throw new RuntimeException("Couldn't get KEY_BYTE indices");
        }

        $keyByteIndices = array_map('intval', $matches[1]);

        $this->rowIndex = $keyByteIndices[0];
        $this->keyBytesIndices = array_slice($keyByteIndices, 1);

        return [$this->rowIndex, $this->keyBytesIndices];
    }

    public function setIndices(int $rowIndex, array $keyBytesIndices): self
    {
        $this->rowIndex = $rowIndex;
        $this->keyBytesIndices = $keyBytesIndices;

        return $this;
    }
}