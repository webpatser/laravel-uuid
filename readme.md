# Laravel Uuid

[![Total Downloads](https://poser.pugx.org/webpatser/laravel-uuid/downloads.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Build Status](https://secure.travis-ci.org/webpatser/laravel-uuid.png?branch=master)](http://travis-ci.org/webpatser/laravel-uuid)
[![codecov.io](http://codecov.io/github/webpatser/laravel-uuid/coverage.svg?branch=master)](http://codecov.io/github/webpatser/laravel-uuid?branch=master)
[![Latest Stable Version](https://poser.pugx.org/webpatser/laravel-uuid/v/stable.svg)](https://packagist.org/packages/webpatser/laravel-uuid)
[![Licence](https://poser.pugx.org/webpatser/laravel-uuid/license.svg)](https://packagist.org/packages/webpatser/laravel-uuid)

Laravel package to generate a UUID according to the RFC 4122 standard. Support for version 1, 3, 4 and 5 UUIDs are built-in.

## What's new in 3.*
Laravel-uuid is now refactored for Laravel 5.5. It has the same requirements so that means PHP 7. Laravel package auto-discovery is enabled, and you can now use the UUID validation. Validation examples are below and in the tests. 

Laravel 5.0, 5.1, 5.2, 5.3 and 5.4? use the [version 2 branch](https://github.com/webpatser/laravel-uuid/tree/2.1.1)

Laravel 4.*? use the [version 1 branch](https://github.com/webpatser/laravel-uuid/tree/1.5)

## Installation


In Laravel 5.5 laravel-uuid will install via the new package discovery feature so you only need to add the package to your composer.json file

```shell
composer require "webpatser/laravel-uuid:^3.0"
```

## Basic Usage

To quickly generate a UUID just do

```php
Uuid::generate()
```
	
This will generate a version 1 Uuid `object` with a random ganerated MAC address.

To echo out the generated Uuid cast it to a string

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
	
### Some magic features

To import a UUID

```php
$uuid = Uuid::import('d3d29d70-1d25-11e3-8591-034165a3a613');
```	

Extract the time for a time-based UUID (version 1)

```php
$uuid = Uuid::generate(1);
dd($uuid->time);
```

Extract the version of an UUID

```php
$uuid = Uuid::generate(4);
dd($uuid->version);
```

## Eloquent uuid generation

If you want an UUID magically be generated in your Laravel models, just add this boot function to your Model.

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

## Notes

Full details on the UUID specification can be found [here](http://tools.ietf.org/html/rfc4122)
