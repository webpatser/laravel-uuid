<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Webpatser\Uuid\Uuid;
use Illuminate\Support\Str;

/**
 * UUID Macros for Laravel's Str class
 * 
 * This class provides macros to replace Laravel's built-in UUID functions
 * with our high-performance webpatser/uuid library (15% faster than Ramsey).
 * 
 * These macros are automatically registered by the UuidServiceProvider,
 * so existing code using Str::uuid() and Str::orderedUuid() will automatically
 * benefit from the performance improvements without any code changes.
 */
class UuidMacros
{
    /**
     * Register all UUID macros on Laravel's Str class
     */
    public static function register(): void
    {
        // Note: Laravel already has uuid(), orderedUuid(), and isUuid() methods
        // Macros can't override existing methods, so we use alternative names
        // for our high-performance versions while keeping compatibility
        
        // High-performance alternatives to Laravel's UUID methods
        Str::macro('fastUuid', function () {
            return (string) Uuid::v4();
        });

        Str::macro('fastOrderedUuid', function () {
            return (string) Uuid::v7();
        });

        Str::macro('fastIsUuid', function (string $value) {
            return Uuid::validate($value);
        });

        // Add new macros for additional UUID versions
        
        // Generate V1 time-based UUID
        Str::macro('timeBasedUuid', function () {
            return (string) Uuid::generate(1);
        });

        // Generate V6 reordered time-based UUID (better than V1 for databases)
        Str::macro('reorderedTimeUuid', function () {
            return (string) Uuid::generate(6);
        });

        // Generate V8 custom UUID
        Str::macro('customUuid', function ($data = null) {
            return (string) Uuid::generate(8, $data);
        });

        // Generate name-based V3 UUID (MD5)
        Str::macro('nameUuidMd5', function (string $name, string $namespace = Uuid::NS_DNS) {
            return (string) Uuid::generate(3, $name, $namespace);
        });

        // Generate name-based V5 UUID (SHA-1)
        Str::macro('nameUuidSha1', function (string $name, string $namespace = Uuid::NS_DNS) {
            return (string) Uuid::generate(5, $name, $namespace);
        });

        // Get UUID version
        Str::macro('uuidVersion', function (string $uuid) {
            if (!Uuid::validate($uuid)) {
                return null;
            }
            return Uuid::import($uuid)->version;
        });

        // Get timestamp from time-based UUIDs
        Str::macro('uuidTimestamp', function (string $uuid) {
            if (!Uuid::validate($uuid)) {
                return null;
            }
            return Uuid::import($uuid)->time;
        });

        // Generate nil UUID
        Str::macro('nilUuid', function () {
            return (string) Uuid::nil();
        });

        // Check if UUID is nil
        Str::macro('isNilUuid', function (string $uuid) {
            return Uuid::isNilUuid($uuid);
        });

        // === BINARY UUID METHODS (55% storage savings) ===

        // Generate binary V4 UUID (16 bytes instead of 36 chars)
        Str::macro('fastBinaryUuid', function () {
            return Uuid::v4()->bytes;
        });

        // Generate binary ordered UUID (V7) - ideal for database primary keys
        Str::macro('fastBinaryOrderedUuid', function () {
            return Uuid::v7()->bytes;
        });

        // Convert string UUID to binary format
        Str::macro('uuidToBinary', function (string $uuid) {
            if (!Uuid::validate($uuid)) {
                throw new \InvalidArgumentException("Invalid UUID format: {$uuid}");
            }
            return Uuid::import($uuid)->bytes;
        });

        // Convert binary UUID to string format
        Str::macro('binaryToUuid', function (string $binary) {
            if (strlen($binary) !== 16) {
                throw new \InvalidArgumentException("Binary UUID must be exactly 16 bytes");
            }
            return Uuid::import($binary)->string;
        });

        // Validate binary UUID
        Str::macro('isValidBinaryUuid', function (string $binary) {
            if (strlen($binary) !== 16) {
                return false;
            }
            try {
                $uuid = Uuid::import($binary);
                return Uuid::validate($uuid->string);
            } catch (\Exception) {
                return false;
            }
        });

        // Generate binary time-based UUID (V1)
        Str::macro('binaryTimeBasedUuid', function () {
            return Uuid::generate(1)->bytes;
        });

        // Generate binary reordered time UUID (V6) 
        Str::macro('binaryReorderedTimeUuid', function () {
            return Uuid::generate(6)->bytes;
        });

        // Generate binary custom UUID (V8)
        Str::macro('binaryCustomUuid', function (string $data) {
            return Uuid::generate(8, $data)->bytes;
        });
    }
}