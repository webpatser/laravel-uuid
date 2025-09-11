# Laravel UUID Integration

Laravel package for generating and working with UUIDs. Automatic UUID model keys, validation rules, and Eloquent support.

## Installation

```bash
composer require webpatser/laravel-uuid
```

**Requirements:** PHP 8.2+, Laravel 11.x or 12.x

## Quick Start

```php
use Illuminate\Support\Str;
use Webpatser\LaravelUuid\HasUuids;

// High-performance UUID generation
$uuid = Str::fastUuid();                // 15% faster than Str::uuid()
$ordered = Str::fastOrderedUuid();      // Database-optimized

// Eloquent model integration
class User extends Model 
{
    use HasUuids; // Automatic UUID generation
}
```

## Documentation

For complete documentation, examples, and API reference, visit:

**https://documentation.downsized.nl/laravel-uuid**

## License

MIT License.