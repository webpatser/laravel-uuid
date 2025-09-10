<?php

declare(strict_types=1);

namespace Webpatser\Uuid;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class UuidServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Validator::extend('uuid', fn($attribute, $value, $parameters, $validator) => Uuid::validate($value));
    }
    
    public function register(): void
    {
        //
    }
}
