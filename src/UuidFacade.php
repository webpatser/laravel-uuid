<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Illuminate\Support\Facades\Facade;

/**
 * Laravel Facade for UUID functionality
 *
 * @method static \Webpatser\Uuid\Uuid generate(int $ver = 1, mixed $node = null, ?string $ns = null)
 * @method static \Webpatser\Uuid\Uuid v4()
 * @method static \Webpatser\Uuid\Uuid v7()
 * @method static bool validate(mixed $uuid)
 * @method static \Webpatser\Uuid\Uuid import(string $uuid)
 * @method static bool compare(string|\Webpatser\Uuid\Uuid $uuid1, string|\Webpatser\Uuid\Uuid $uuid2)
 * @method static \Webpatser\Uuid\Uuid nil()
 * @method static bool isNilUuid(string|\Webpatser\Uuid\Uuid $uuid)
 * @method static array benchmark(int $iterations = 10000, int $version = 4)
 *
 * @see \Webpatser\Uuid\Uuid
 */
class UuidFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Webpatser\Uuid\Uuid::class;
    }
}
