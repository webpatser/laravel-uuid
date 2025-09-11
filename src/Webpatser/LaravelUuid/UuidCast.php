<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

/**
 * Laravel Eloquent cast for UUID columns
 *
 * Usage in your Eloquent models:
 * protected $casts = [
 *     'id' => UuidCast::class,
 *     'user_id' => UuidCast::class,
 * ];
 */
class UuidCast implements CastsAttributes
{
    /**
     * Cast the given value to a UUID instance
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Uuid
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value;
        }

        return Uuid::import((string) $value);
    }

    /**
     * Prepare the given value for storage
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Uuid) {
            return $value->string;
        }

        if (is_string($value) && Uuid::validate($value)) {
            return $value;
        }

        // Try to import/validate the value
        return Uuid::import((string) $value)->string;
    }
}
