<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Illuminate\Database\Eloquent\Concerns\HasUniqueStringIds;
use Webpatser\Uuid\Uuid;

/**
 * High-performance HasBinaryUuids trait for binary UUID storage
 *
 * This trait enables storing UUIDs as 16-byte binary data instead of 36-character strings,
 * providing significant database storage and performance benefits:
 *
 * - 55% smaller storage (16 bytes vs 36 bytes)
 * - Faster database queries and indexing
 * - Better clustering for sequential UUIDs (V7)
 * - Reduced memory usage
 *
 * Usage:
 * class User extends Model {
 *     use \Webpatser\LaravelUuid\HasBinaryUuids;
 * }
 *
 * Migration:
 * $table->binary('id', 16)->primary();
 *
 * Features:
 * - Automatic binary conversion on save/load
 * - Uses high-performance webpatser/uuid library
 * - Compatible with all UUID versions (1,3,4,5,6,7,8)
 * - Route model binding support
 * - Proper casting and validation
 */
trait HasBinaryUuids
{
    use HasUniqueStringIds;

    /**
     * Generate a new UUID for the model
     *
     * Override this method to customize UUID generation:
     * - Return Uuid::v4() for random UUIDs
     * - Return Uuid::v7() for timestamp-ordered UUIDs (recommended for databases)
     * - Return Uuid::generate(1) for time-based UUIDs with MAC address
     */
    public function newUniqueId(): string
    {
        // Use V7 UUIDs by default for optimal database performance
        // V7 UUIDs are naturally sortable and provide better database clustering
        $uuid = Uuid::v7();

        return $uuid->bytes; // Return binary data instead of string
    }

    /**
     * Determine if the given value is a valid UUID (string or binary)
     *
     * Uses our high-performance UUID validation for both formats
     */
    protected function isValidUniqueId(mixed $value): bool
    {
        if (is_string($value)) {
            // Check if it's a 16-byte binary UUID
            if (strlen($value) === 16) {
                // Convert binary to string format for validation
                $uuid = Uuid::import($value);

                return Uuid::validate($uuid->string);
            }

            // Regular string UUID validation
            return Uuid::validate($value);
        }

        return false;
    }

    /**
     * Convert UUID from database binary format to string for display
     */
    public function getUuidAsString(?string $column = null): string
    {
        $column = $column ?? $this->getKeyName();
        $binaryUuid = $this->getAttribute($column);

        if (! $binaryUuid || strlen($binaryUuid) !== 16) {
            return '';
        }

        return Uuid::import($binaryUuid)->string;
    }

    /**
     * Convert string UUID to binary format for database storage
     */
    public function setUuidFromString(string $uuid, ?string $column = null): void
    {
        $column = $column ?? $this->getKeyName();

        if (Uuid::validate($uuid)) {
            $this->setAttribute($column, Uuid::import($uuid)->bytes);
        }
    }

    /**
     * Get the route key for the model (convert binary to string)
     */
    public function getRouteKey(): string
    {
        $key = $this->getAttribute($this->getRouteKeyName());

        if ($key && strlen($key) === 16) {
            return Uuid::import($key)->string;
        }

        return $key ?? '';
    }

    /**
     * Resolve route model binding for binary UUIDs
     */
    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        // Convert string UUID to binary for database query
        if (is_string($value) && Uuid::validate($value)) {
            $value = Uuid::import($value)->bytes;
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

    /**
     * Generate a random UUID (Version 4) in binary format
     */
    public function newRandomBinaryUuid(): string
    {
        return Uuid::v4()->bytes;
    }

    /**
     * Generate a timestamp-ordered UUID (Version 7) in binary format
     */
    public function newOrderedBinaryUuid(): string
    {
        return Uuid::v7()->bytes;
    }

    /**
     * Generate a time-based UUID (Version 1) in binary format
     */
    public function newTimeBasedBinaryUuid(): string
    {
        return Uuid::generate(1)->bytes;
    }

    /**
     * Get UUID version from the model's primary key
     */
    public function getUuidVersion(): ?int
    {
        $key = $this->getKey();

        if (! $key) {
            return null;
        }

        // Handle binary format
        if (strlen($key) === 16) {
            return Uuid::import($key)->version;
        }

        // Handle string format
        if (Uuid::validate($key)) {
            return Uuid::import($key)->version;
        }

        return null;
    }

    /**
     * Check if the model uses timestamp-ordered UUIDs (V7)
     */
    public function usesOrderedUuids(): bool
    {
        return $this->getUuidVersion() === 7;
    }

    /**
     * Get the timestamp from a UUID (if it's time-based)
     */
    public function getUuidTimestamp(): ?float
    {
        $key = $this->getKey();

        if (! $key) {
            return null;
        }

        $uuid = strlen($key) === 16 ? Uuid::import($key) :
                (Uuid::validate($key) ? Uuid::import($key) : null);

        return $uuid?->time;
    }

    /**
     * Get the auto-incrementing key type
     */
    public function getKeyType(): string
    {
        return 'string'; // Binary data is stored as string in PHP
    }

    /**
     * Get the value indicating whether the IDs are incrementing
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
