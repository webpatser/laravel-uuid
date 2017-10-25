<?php
namespace Webpatser\Uuid;

use Illuminate\Support\ServiceProvider;

class UuidLumenServiceProvider extends ServiceProvider
{

    /**
     * Register UUID
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('uuid', function ($app) {
            return Uuid::generate();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['validator']->extend('uuid', function ($attribute, $value, $parameters, $validator) {
            return $this->app['uuid']->validate($value);
        });
    }
}
