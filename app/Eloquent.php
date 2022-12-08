<?php

namespace Siluet;

use Illuminate\Database\Capsule\Manager;

class Eloquent
{
    private static Manager $capsule;

    public static function boot()
    {
        static::$capsule = new Manager();

        static::$capsule->addConnection([
            'driver' => 'mysql',
            'host' => $_ENV["DB_HOST"],
            'database' => $_ENV["DB_NAME"],
            'username' => $_ENV["DB_USER"],
            'password' => $_ENV["DB_PASS"],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        // Make this Capsule instance available globally via static methods... (optional)
        static::$capsule->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        static::$capsule->bootEloquent();
    }

    public static function getCapsule()
    {
        return static::$capsule;
    }
}
