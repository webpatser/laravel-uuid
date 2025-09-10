<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Webpatser\Uuid\Uuid;
use Illuminate\Database\Eloquent\Concerns\HasUniqueStringIds;

/**
 * High-performance HasUuids trait using webpatser/uuid library
 * 
 * This trait replaces Laravel's built-in HasUuids trait which uses Ramsey UUID.
 * Our implementation is 15% faster with modern PHP 8.2+ optimizations.
 * 
 * Usage:
 * class User extends Model {
 *     use \Webpatser\LaravelUuid\HasUuids;
 * }
 * 
 * Features:
 * - Automatic UUID generation on model creation
 * - Uses high-performance webpatser/uuid library (15% faster than Ramsey)
 * - Supports UUID v4 (random) and v7 (timestamp-ordered) 
 * - Proper route model binding integration
 * - Customizable UUID columns and generation
 */
trait HasUuids
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
        return (string) Uuid::v7();
    }

    /**
     * Determine if the given value is a valid UUID
     * 
     * Uses our high-performance UUID validation
     */
    protected function isValidUniqueId(mixed $value): bool
    {
        return Uuid::validate($value);
    }

    /**
     * Generate a random UUID (Version 4)
     * 
     * Helper method for models that prefer random UUIDs over ordered ones
     */
    public function newRandomUuid(): string
    {
        return (string) Uuid::v4();
    }

    /**
     * Generate a timestamp-ordered UUID (Version 7)
     * 
     * Helper method for models that want explicit V7 UUIDs
     * V7 UUIDs are ideal for database primary keys as they maintain sort order
     */
    public function newOrderedUuid(): string
    {
        return (string) Uuid::v7();
    }

    /**
     * Generate a time-based UUID (Version 1)
     * 
     * Helper method for models that need time-based UUIDs with MAC address
     * Note: Exposes MAC address, consider V7 for better privacy
     */
    public function newTimeBasedUuid(): string
    {
        return (string) Uuid::generate(1);
    }

    /**
     * Get UUID version from the model's primary key
     * 
     * Returns null if the primary key is not a valid UUID
     */
    public function getUuidVersion(): ?int
    {
        $id = $this->getKey();
        
        if (!$id || !Uuid::validate($id)) {
            return null;
        }
        
        return Uuid::import($id)->version;
    }

    /**
     * Check if the model uses timestamp-ordered UUIDs (V7)
     * 
     * Useful for determining if the model's UUIDs are naturally sortable
     */
    public function usesOrderedUuids(): bool
    {
        return $this->getUuidVersion() === 7;
    }

    /**
     * Get the timestamp from a UUID (if it's time-based)
     * 
     * Returns the timestamp for V1, V6, and V7 UUIDs
     * Returns null for other UUID versions or invalid UUIDs
     */
    public function getUuidTimestamp(): ?float
    {
        $id = $this->getKey();
        
        if (!$id || !Uuid::validate($id)) {
            return null;
        }
        
        $uuid = Uuid::import($id);
        return $uuid->time;
    }
}