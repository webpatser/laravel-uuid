<?php

declare(strict_types=1);

namespace Webpatser\LaravelUuid;

use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class UuidServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register UUID macros to replace Laravel's Str UUID functions
        // with our high-performance library (15% faster than Ramsey UUID)
        UuidMacros::register();
    }

    public function register(): void
    {
        // No explicit registration needed - facade uses static methods directly
        // Laravel's auto-discovery handles the facade alias registration
    }
}
