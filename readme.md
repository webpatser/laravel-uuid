# Laravel Uuid
Laravel package to generate a UUID according to the RFC 4122 standard. Only support for version 1, 3, 4 and 5 UUID are built-in.


## Installation

Add `webpatser/laravel-uuid` to `composer.json`.

    "webpatser/laravel-uuid": "dev-master"
    
Run `composer update` to pull down the latest version of Country List.

Edit `app/config/app.php` and add the `alias`

    'aliases' => array(
        'Uuid' => 'Webpatser\Uuid\Uuid',
    )

    
## Usage

Generate a version 1 UUID

	Uuid::generate(1,'00:11:22:33:44:55');
	
Generate a version 3 UUID

	Uuid::generate(3,'test','6ba7b810-9dad-11d1-80b4-00c04fd430c8');
	
Generate a version 4 UUID

	Uuid::generate(4);

Generate a version 5 UUID

	Uuid::generate(5,'test','6ba7b810-9dad-11d1-80b4-00c04fd430c8');
	
## Notes

Full details on the UUID specification can be found [here](http://tools.ietf.org/html/rfc4122)

If used on windows it will use the [CAPICOM getRandom method](http://msdn.microsoft.com/en-us/library/aa388182(VS.85).aspx)