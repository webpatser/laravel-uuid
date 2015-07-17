<?php

namespace Webpatser\Uuid\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;

class UuidServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap the UUID service.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadTranslationsFrom(__DIR__ . '../Lang', 'uuid');
		Validator::extend('uuid', function($attribute, $value, $parameters) {
			return \Uuid::isValid($value);
		});
	}

	/**
	 * Register the UUID service provider.
	 *
	 * @return void
	 */
	public function register()
	{

	}
}