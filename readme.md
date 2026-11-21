# Laravel UUID Integration

[![Total Downloads](https://img.shields.io/packagist/dt/webpatser/laravel-uuid.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![PHP Version](https://img.shields.io/packagist/php-v/webpatser/laravel-uuid.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Laravel Version](https://img.shields.io/badge/laravel-^13.0-red.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![License](https://img.shields.io/packagist/l/webpatser/laravel-uuid.svg)](https://packagist.org/packages/webpatser/laravel-uuid)

Laravel package for generating and working with UUIDs. Automatic UUID model keys, validation rules, and Eloquent support.

## Installation

```bash
composer require webpatser/laravel-uuid
```

**Requirements:** PHP 8.6+, Laravel 13.x

## PHP version policy

This package supports only the latest stable PHP release. When a new PHP version is released, the next release of this package requires it and support for the previous PHP version ends. Currently: PHP 8.6.

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