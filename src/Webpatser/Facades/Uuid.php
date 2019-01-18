<?php

namespace Webpatser\Uuid\Facades;

use Illuminate\Support\Facades\Facade;

class Uuid extends Facade
{
    /**
     * @see \Webpatser\Uuid\Uuid
     */
    protected static function getFacadeAccessor() : string
    {
        return 'uuid';
    }
}
