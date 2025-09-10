# Laravel Uuid

[![Total Downloads](https://poser.pugx.org/webpatser/laravel-uuid/downloads.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Build Status](https://secure.travis-ci.org/webpatser/laravel-uuid.png?branch=master)](http://travis-ci.org/webpatser/laravel-uuid)
[![codecov.io](http://codecov.io/github/webpatser/laravel-uuid/coverage.svg?branch=master)](http://codecov.io/github/webpatser/laravel-uuid?branch=master)
[![Latest Stable Version](https://poser.pugx.org/webpatser/laravel-uuid/v/stable.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Licence](https://poser.pugx.org/webpatser/laravel-uuid/license.svg)](https://packagist.org/packages/webpatser/laravel-uuid)

Laravel package to generate and to validate universally unique identifiers (UUIDs) according to both RFC 4122 and the modern RFC 9562 standards. Support for UUID versions 1, 3, 4, 5, 6, 7, and 8 are built-in.

## What's new in 5.*
Laravel-uuid v5 is a major modernization update requiring **PHP 8.0+**. This version includes:

- ✨ **Complete RFC 9562 support** - All modern UUID versions (6, 7, 8)
- 🚀 **PHP 8+ optimizations** - Strict types, match expressions, union types
- ⚡ **Performance improvements** - Up to 25% faster UUID generation
- 🛡️ **Enhanced security** - Modern cryptographic random generation
- 🔧 **Nil UUID support** - Built-in nil UUID handling
- 📦 **Laravel auto-discovery** enabled

**Requires PHP 8.0+ and modern Laravel versions.**

## What's new in 4.*
Laravel-uuid v4 supports PHP 7.3, 7.4, and 8.x. It includes Laravel package auto-discovery and UUID validation support.

**For PHP 7.x projects, use version 4.x**

For older Laravel or PHP versions use older versions; see below...

## What's new in 3.*
Laravel-uuid is now refactored for Laravel 5.5. It has the same requirements so that means PHP 7. Laravel package auto-discovery is enabled, and you can now use the UUID validation. Validation examples are below and in the tests. 

Laravel 5.0, 5.1, 5.2, 5.3 and 5.4? use  [version 2](https://github.com/webpatser/laravel-uuid/tree/2.1.1)

Laravel 4.*? use [version 1](https://github.com/webpatser/laravel-uuid/tree/1.5)

## Installation

### Version 5.x (PHP 8.0+)
For modern PHP 8.0+ projects with complete RFC 9562 UUID support:

```shell
composer require "webpatser/laravel-uuid:^5.0"
```

### Version 4.x (PHP 7.3+ & 8.x)
For projects still using PHP 7.3+ or mixed PHP 7/8 environments:

```shell
composer require "webpatser/laravel-uuid:^4.0"
```

Laravel package auto-discovery is enabled, so after installation you should see:

```shell
Discovered Package: webpatser/laravel-uuid
```

and you are ready to go

## Basic Usage

To quickly generate a UUID just do

```php
Uuid::generate()
```
	
This will generate a version 1 Uuid `object` with a random generated MAC address.

To echo out the generated UUID, cast it to a string

```php
(string) Uuid::generate()
```

or

```php
Uuid::generate()->string
```

## Advanced Usage

### UUID creation

Generate a version 1, time-based, UUID. You can set the optional node to the MAC address. If not supplied it will generate a random MAC address.

```php
Uuid::generate(1,'00:11:22:33:44:55');
```
	
Generate a version 3, name-based using MD5 hashing, UUID

```php
Uuid::generate(3,'test', Uuid::NS_DNS);
```	

Generate a version 4, truly random, UUID

```php
Uuid::generate(4);
```

Generate a version 5, name-based using SHA-1 hashing, UUID

```php
Uuid::generate(5,'test', Uuid::NS_DNS);
```

Generate a version 6, time-reordered UUID (better database performance than v1)

```php
Uuid::generate(6);
Uuid::generate(6, '00:11:22:33:44:55'); // With MAC address
```

Generate a version 7, Unix Epoch time UUID (recommended for new applications)

```php
Uuid::generate(7);
```

Generate a version 8, custom/vendor UUID

```php
Uuid::generate(8);                    // Random implementation
Uuid::generate(8, 'custom-data');     // Deterministic from data
Uuid::generate(8, ['key' => 'value']); // From complex data
```

### Nil UUID Support

Create and work with the special nil UUID (00000000-0000-0000-0000-000000000000):

```php
$nil = Uuid::nil();
echo $nil->isNil(); // true

// Check if any UUID is nil
Uuid::isNilUuid('00000000-0000-0000-0000-000000000000'); // true
Uuid::isNilUuid($someUuid); // false (for regular UUIDs)

// Use the nil constant
echo Uuid::NIL; // '00000000-0000-0000-0000-000000000000'
```

### Modern UUID Versions (RFC 9562)

**Version 7 - Recommended for new applications:**
- Unix timestamp in milliseconds (sortable)
- No MAC address exposure (privacy-friendly) 
- Database-optimized (clustered indexing)

**Version 6 - For migrating from Version 1:**
- Same as V1 but reordered for better sorting
- Maintains MAC address compatibility
- Better database locality than V1

**Version 8 - For custom implementations:**
- 122 bits customizable
- RFC-compliant format
- Deterministic from input data

### Some magic features

To import a UUID

```php
$uuid = Uuid::import('d3d29d70-1d25-11e3-8591-034165a3a613');
```	

Extract the time for time-based UUIDs (versions 1, 6, 7)

```php
$uuid = Uuid::generate(1);
dd($uuid->time); // Gregorian timestamp

$uuid6 = Uuid::generate(6);
dd($uuid6->time); // Gregorian timestamp (same as v1)

$uuid7 = Uuid::generate(7);
dd($uuid7->time); // Unix timestamp (seconds since epoch)
```

Extract the version of an UUID

```php
$uuid = Uuid::generate(4);
dd($uuid->version);
```

## Eloquent UUID generation

If you want an UUID magically be generated in your Laravel models, just add this boot method to your Model.

```php
/**
 *  Setup model event hooks
 */
public static function boot()
{
    parent::boot();
    self::creating(function ($model) {
        $model->uuid = (string) Uuid::generate(4);
    });
}
```
This will generate a version 4 UUID when creating a new record.

**Recommended:** For new applications, consider using Version 7 for better database performance:

```php
public static function boot()
{
    parent::boot();
    self::creating(function ($model) {
        $model->uuid = (string) Uuid::generate(7); // Better for databases
    });
}
```

## Model binding to UUID instead of primary key

If  you want to use the UUID in URLs instead of the primary key, you can add this to your model (where 'uuid' is the column name to store the UUID)

```php
/**
 * Get the route key for the model.
 *
 * @return string
 */
public function getRouteKeyName()
{
    return 'uuid';
}
```

When you inject the model on your resource controller methods you get the correct record

```php
public function edit(Model $model)
{
   return view('someview.edit')->with([
        'model' => $model,
    ]);
}
```

## Validation

Just use like any other Laravel validator.

``'uuid-field' => 'uuid'``

Or create a validator from scratch. In the example an Uuid object in validated. You can also validate strings `$uuid->string`, the URN `$uuid->urn` or the binary value `$uuid->bytes`

```php
$uuid = Uuid::generate();
$validator = Validator::make(['uuid' => $uuid], ['uuid' => 'uuid']);
dd($validator->passes());
```

## Version Comparison

| Version | Type | Use Case | Performance | Privacy |
|---------|------|----------|-------------|---------|
| 1 | Time + MAC | Legacy systems | Poor sorting | MAC exposed |
| 3 | MD5 name-based | Deterministic from name | N/A | Good |
| 4 | Random | General purpose | Poor sorting | Excellent |
| 5 | SHA-1 name-based | Deterministic from name | N/A | Good |
| 6 | Time + MAC reordered | V1 migration | Good sorting | MAC exposed |
| 7 | Unix time + random | **New applications** | **Best sorting** | **Excellent** |
| 8 | Custom | Special requirements | Varies | Varies |

**Recommendation**: Use Version 7 for new applications requiring sortable UUIDs, or Version 4 for maximum randomness.

## PHP Version Support

| Package Version | PHP Version | UUID Versions | Status |
|----------------|-------------|---------------|---------|
| 5.x | 8.0+ | 1, 3, 4, 5, 6, 7, 8 | ✅ Current (RFC 9562) |
| 4.x | 7.3 - 8.x | 1, 3, 4, 5 | ✅ Legacy support |
| 3.x | 7.0+ | 1, 3, 4, 5 | 🔒 Security only |
| 2.x | 5.4+ | 1, 3, 4, 5 | ❌ End of life |

## Notes

Full details on the UUID specifications:
- [RFC 4122](http://tools.ietf.org/html/rfc4122) - Original UUID standard
- [RFC 9562](https://www.rfc-editor.org/rfc/rfc9562.html) - Modern UUID standard (2024)
