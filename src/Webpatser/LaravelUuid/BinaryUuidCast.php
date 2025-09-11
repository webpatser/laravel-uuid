<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Webpatser\Uuid\Uuid;

/**
 * Binary UUID Cast for Eloquent
 *
 * Automatically converts between binary UUID storage (16 bytes) and UUID objects/strings.
 * Provides 55% storage savings compared to string UUIDs.
 *
 * Usage:
 * protected $casts = [
 *     'id' => BinaryUuidCast::class,
 *     'user_id' => BinaryUuidCast::class,
 * ];
 *
 * Benefits:
 * - 55% smaller database storage (16 bytes vs 36 bytes)
 * - Faster database queries and indexing
 * - Better memory usage
 * - Automatic conversion in both directions
 *
 * Database Migration:
 * $table->binary('id', 16)->primary();
 * $table->binary('user_id', 16)->nullable();
 */
class BinaryUuidCast implements CastsAttributes
{
    /**
     * Cast the given value from binary to UUID object
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Uuid
    {
        if ($value === null) {
            return null;
        }

        // If it's already a UUID object, return it
        if ($value instanceof Uuid) {
            return $value;
        }

        // Handle binary format (16 bytes)
        if (is_string($value) && strlen($value) === 16) {
            return Uuid::import($value);
        }

        // Handle string format (36 chars) - fallback for mixed storage
        if (is_string($value) && strlen($value) === 36 && Uuid::validate($value)) {
            return Uuid::import($value);
        }

        // Invalid format
        throw new InvalidArgumentException("Invalid UUID format for key '{$key}': ".
            (is_string($value) ? 'length '.strlen($value) : gettype($value)));
    }

    /**
     * Cast the given UUID to binary format for database storage
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Handle UUID objects
        if ($value instanceof Uuid) {
            return $value->bytes;
        }

        // Handle string UUIDs
        if (is_string($value)) {
            // Already binary format
            if (strlen($value) === 16) {
                return $value;
            }

            // String format - convert to binary
            if (strlen($value) === 36 && Uuid::validate($value)) {
                return Uuid::import($value)->bytes;
            }
        }

        // Invalid format
        throw new InvalidArgumentException("Cannot cast value to binary UUID for key '{$key}': ".
            (is_string($value) ? "'{$value}'" : gettype($value)));
    }

    /**
     * Get the serialized representation of the value
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value->string;
        }

        // If it's binary, convert to string for JSON serialization
        if (is_string($value) && strlen($value) === 16) {
            return Uuid::import($value)->string;
        }

        return $value;
    }
}
