<?php

declare(strict_types=1);

namespace Webpatser\Uuid;

use Exception;

/**
 * Class Uuid
 * @package Webpatser\Uuid
 *
 * @property string $bytes
 * @property string $hex
 * @property string $node
 * @property string $string
 * @property string $uuid_ordered
 * @property string $time
 * @property string $urn
 * @property string $variant
 * @property string $version
 *
 */
class Uuid
{
    public const MD5 = 3;
    public const SHA1 = 5;
    
    public const CLEAR_VER = 15;
    public const CLEAR_VAR = 63;
    public const VAR_RES = 224;
    public const VAR_MS = 192;
    public const VAR_RFC = 128;
    public const VAR_NCS = 0;
    public const VERSION_1 = 16;
    public const VERSION_3 = 48;
    public const VERSION_4 = 64;
    public const VERSION_5 = 80;
    public const VERSION_6 = 96;
    public const VERSION_7 = 112;
    public const VERSION_8 = 128;
    public const INTERVAL = 0x01b21dd213814000;
    public const NS_DNS = '6ba7b810-9dad-11d1-80b4-00c04fd430c8';
    public const NS_URL = '6ba7b811-9dad-11d1-80b4-00c04fd430c8';
    public const NS_OID = '6ba7b812-9dad-11d1-80b4-00c04fd430c8';
    public const NS_X500 = '6ba7b814-9dad-11d1-80b4-00c04fd430c8';
    public const NIL = '00000000-0000-0000-0000-000000000000';
    public const VALID_UUID_REGEX = '^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$';

    protected string $bytes;
    protected string $string;
    protected string $uuid_ordered;

    protected function __construct(string $uuid)
    {
        if (!empty($uuid) && strlen($uuid) !== 16) {
            throw new Exception('Input must be a 128-bit integer.');
        }
        
        $this->bytes = $uuid;
        
        // Optimize the most common use
        $this->string = bin2hex(substr($uuid, 0, 4)) . "-" .
            bin2hex(substr($uuid, 4, 2)) . "-" .
            bin2hex(substr($uuid, 6, 2)) . "-" .
            bin2hex(substr($uuid, 8, 2)) . "-" .
            bin2hex(substr($uuid, 10, 6));

        // Store UUID in an optimized way
        $this->uuid_ordered = bin2hex(substr($uuid, 6, 2)) .
            bin2hex(substr($uuid, 4, 2)) .
            bin2hex(substr($uuid, 0, 4));
    }
    
    
    public static function generate(int $ver = 1, mixed $node = null, ?string $ns = null): self
    {
        /* Create a new UUID based on provided data. */
        switch ((int)$ver) {
            case 1:
                return new static(static::mintTime($node));
            case 2:
                // Version 2 is not supported
                throw new Exception('Version 2 is unsupported.');
            case 3:
                return new static(static::mintName(static::MD5, $node, $ns));
            case 4:
                return new static(static::mintRand());
            case 5:
                return new static(static::mintName(static::SHA1, $node, $ns));
            case 6:
                return new static(static::mintTimeV6($node));
            case 7:
                return new static(static::mintTimeV7());
            case 8:
                return new static(static::mintCustomV8($node));
            default:
                throw new Exception('Selected version is invalid or unsupported.');
        }
    }
    
    protected static function mintTime(?string $node = null): string
    {
        
        /** Get time since Gregorian calendar reform in 100ns intervals
         * This is exceedingly difficult because of PHP's (and pack()'s)
         * integer size limits.
         * Note that this will never be more accurate than to the microsecond.
         */
        $time = microtime(true) * 10000000 + static::INTERVAL;
        
        // Convert to a string representation
        $time = sprintf("%F", $time);
        
        //strip decimal point
        preg_match("/^\d+/", $time, $time);
        
        // And now to a 64-bit binary representation
        $time = base_convert($time[0], 10, 16);
        $time = pack("H*", str_pad($time, 16, "0", STR_PAD_LEFT));
        
        // Reorder bytes to their proper locations in the UUID
        $uuid = $time[4] . $time[5] . $time[6] . $time[7] . $time[2] . $time[3] . $time[0] . $time[1];
        
        // Generate a random clock sequence
        $uuid .= static::randomBytes(2);
        
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        
        // set version
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_1);
        
        // Set the final 'node' parameter, a MAC address
        if (!is_null($node)) {
            $node = static::makeBin($node, 6);
        }
        
        // If no node was provided or if the node was invalid,
        //  generate a random MAC address and set the multicast bit
        if (is_null($node)) {
            $node = static::randomBytes(6);
            $node[0] = pack("C", ord($node[0]) | 1);
        }
        
        $uuid .= $node;
        
        return $uuid;
    }
    
    public static function randomBytes(int $bytes): string
    {
        return random_bytes($bytes);
    }
    
    protected static function makeBin(mixed $str, int $len): ?string
    {
        if ($str instanceof self) {
            return $str->bytes;
        }
        if (strlen($str) === $len) {
            return $str;
        } else {
            // strip URN scheme and namespace
            $str = preg_replace('/^urn:uuid:/is', '', $str);
        }
        // strip non-hex characters
        $str = preg_replace('/[^a-f0-9]/is', '', $str);
        if (strlen($str) !== ($len * 2)) {
            return null;
        } else {
            return pack("H*", $str);
        }
    }
    
    protected static function mintName(int $ver, string $node, string $ns): string
    {
        if (empty($node)) {
            throw new Exception('A name-string is required for Version 3 or 5 UUIDs.');
        }
        
        // if the namespace UUID isn't binary, make it so
        $ns = static::makeBin($ns, 16);
        if (is_null($ns)) {
            throw new Exception('A binary namespace is required for Version 3 or 5 UUIDs.');
        }
        
        $version = null;
        $uuid = null;
        
        switch ($ver) {
            case static::MD5:
                $version = static::VERSION_3;
                $uuid = md5($ns . $node, true);
                break;
            case static::SHA1:
                $version = static::VERSION_5;
                $uuid = substr(sha1($ns . $node, true), 0, 16);
                break;
            default:
                // no default really required here
        }
        
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        
        // set version
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | $version);
        
        return ($uuid);
    }
    
    protected static function mintRand(): string
    {
        $uuid = static::randomBytes(16);
        // set variant
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        // set version
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_4);
        
        return $uuid;
    }

    protected static function mintTimeV7(): string
    {
        // Get Unix timestamp in milliseconds (48 bits)
        $timestampMs = (int)(microtime(true) * 1000);
        
        // Pack timestamp as 48-bit big-endian integer (6 bytes)
        $timestampBytes = '';
        for ($i = 5; $i >= 0; $i--) {
            $timestampBytes .= chr(($timestampMs >> ($i * 8)) & 0xFF);
        }
        
        // Generate 74 random bits (10 bytes, but we'll use 9.25 bytes effectively)
        $randomBytes = static::randomBytes(10);
        
        // Build UUID: 48-bit timestamp + 4-bit version + 12-bit random + 2-bit variant + 62-bit random
        $uuid = $timestampBytes . $randomBytes;
        
        // Set version (bits 48-51 = version 7)
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_7);
        
        // Set variant (bits 64-65 = RFC variant)
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        
        return $uuid;
    }

    protected static function mintTimeV6(?string $node = null): string
    {
        // Get time since Gregorian calendar reform in 100ns intervals (same as V1)
        $time = microtime(true) * 10000000 + static::INTERVAL;
        
        // Convert to a string representation
        $time = sprintf("%F", $time);
        
        // Strip decimal point
        preg_match("/^\d+/", $time, $time);
        
        // And now to a 64-bit binary representation
        $time = base_convert($time[0], 10, 16);
        $time = pack("H*", str_pad($time, 16, "0", STR_PAD_LEFT));
        
        // For V6: Reorder timestamp bytes for better sorting
        // V1 format: time_low(4) + time_mid(2) + time_high(2)
        // V6 format: time_high(4) + time_mid(2) + time_low(2) (most significant first)
        // Reorder: [4,5,6,7] + [2,3] + [0,1]  ->  [0,1,2,3] + [4,5] + [6,7]
        $uuid = $time[4] . $time[5] . $time[6] . $time[7] . $time[2] . $time[3] . $time[0] . $time[1];
        
        // Generate a random clock sequence
        $uuid .= static::randomBytes(2);
        
        // Set variant
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        
        // Set version
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_6);
        
        // Set the final 'node' parameter, a MAC address
        if (!is_null($node)) {
            $node = static::makeBin($node, 6);
        }
        
        // If no node was provided or if the node was invalid,
        // generate a random MAC address and set the multicast bit
        if (is_null($node)) {
            $node = static::randomBytes(6);
            $node[0] = pack("C", ord($node[0]) | 1);
        }
        
        $uuid .= $node;
        
        return $uuid;
    }

    protected static function mintCustomV8(mixed $data = null): string
    {
        // For V8, we allow custom data or default to random
        if ($data === null) {
            // Default implementation: 122 bits of random data
            $uuid = static::randomBytes(16);
        } else if (is_string($data) && strlen($data) === 16) {
            // Accept 16-byte binary data directly
            $uuid = $data;
        } else {
            // For other data types, hash to get consistent 16 bytes
            $hash = hash('sha256', serialize($data), true);
            $uuid = substr($hash, 0, 16);
        }
        
        // Set variant (bits 64-65 = RFC variant)
        $uuid[8] = chr(ord($uuid[8]) & static::CLEAR_VAR | static::VAR_RFC);
        
        // Set version (bits 48-51 = version 8)
        $uuid[6] = chr(ord($uuid[6]) & static::CLEAR_VER | static::VERSION_8);
        
        return $uuid;
    }
    
    public static function import(string $uuid): self
    {
        return new static(static::makeBin($uuid, 16));
    }
    
    public static function compare(string $a, string $b): bool
    {
        if (static::makeBin($a, 16) == static::makeBin($b, 16)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function __get(string $var): mixed
    {
        return match ($var) {
            'bytes' => $this->bytes,
            'hex' => bin2hex($this->bytes),
            'node' => match (ord($this->bytes[6]) >> 4) {
                1, 6 => bin2hex(substr($this->bytes, 10)),
                default => null,
            },
            'string' => $this->__toString(),
            'uuid_ordered' => $this->__toUuidOrdered(),
            'time' => match (ord($this->bytes[6]) >> 4) {
                1 => $this->extractTime(),
                6 => $this->extractTimeV6(),
                7 => $this->extractTimeV7(),
                default => null,
            },
            'urn' => 'urn:uuid:' . $this->__toString(),
            'variant' => $this->getVariant(),
            'version' => ord($this->bytes[6]) >> 4,
            default => null,
        };
    }
    
    public function __toString(): string
    {
        return $this->string;
    }

    public function __toUuidOrdered(): string
    {
        return $this->uuid_ordered;
    }

    private function extractTime(): float
    {
        $time = bin2hex($this->bytes[6] . $this->bytes[7] . $this->bytes[4] . $this->bytes[5] .
            $this->bytes[0] . $this->bytes[1] . $this->bytes[2] . $this->bytes[3]);
        $time[0] = "0";
        return (hexdec($time) - static::INTERVAL) / 10000000;
    }

    private function extractTimeV6(): float
    {
        // For V6: reorder back to standard timestamp format
        // V6 format: time_high(4) + time_mid(2) + time_low(2)
        // Reorder back: [0,1,2,3] + [4,5] + [6,7] -> [6,7] + [4,5] + [0,1,2,3]
        $time = bin2hex($this->bytes[6] . $this->bytes[7] . $this->bytes[4] . $this->bytes[5] .
            $this->bytes[0] . $this->bytes[1] . $this->bytes[2] . $this->bytes[3]);
        // Clear version flag
        $time[0] = "0";
        // Do some reverse arithmetic to get a Unix timestamp
        return (hexdec($time) - static::INTERVAL) / 10000000;
    }

    private function extractTimeV7(): float
    {
        // Extract 48-bit timestamp from first 6 bytes
        $timestampMs = 0;
        for ($i = 0; $i < 6; $i++) {
            $timestampMs = ($timestampMs << 8) | ord($this->bytes[$i]);
        }
        // Convert milliseconds to seconds (float)
        return $timestampMs / 1000.0;
    }

    private function getVariant(): int
    {
        $byte = ord($this->bytes[8]);
        return match (true) {
            $byte >= static::VAR_RES => 3,
            $byte >= static::VAR_MS => 2,
            $byte >= static::VAR_RFC => 1,
            default => 0,
        };
    }
    
    public static function validate(mixed $uuid): bool
    {
        if ($uuid instanceof self) {
            return (bool) preg_match('~' . static::VALID_UUID_REGEX . '~', $uuid->string);
        }
        return (bool) preg_match('~' . static::VALID_UUID_REGEX . '~', static::import($uuid)->string);
    }

    public static function nil(): self
    {
        return static::import(static::NIL);
    }

    public function isNil(): bool
    {
        return $this->string === static::NIL;
    }

    public static function isNilUuid(mixed $uuid): bool
    {
        if ($uuid instanceof self) {
            return $uuid->isNil();
        }
        return static::import($uuid)->isNil();
    }
}
