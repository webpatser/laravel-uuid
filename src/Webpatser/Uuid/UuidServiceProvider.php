<?php

declare(strict_types=1);

namespace Webpatser\Uuid;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class UuidServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('uuid', fn ($attribute, $value, $parameters, $validator) => Uuid::validate($value));
    }

    public function register(): void
    {
        //
    }
}
