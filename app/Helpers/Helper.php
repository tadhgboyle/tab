<?php

namespace App\Helpers;

use App;

abstract class Helper
{
    private static $_instances = [];

    /**
     * Get the instance of the child helper class.
     * 
     * @return static Instance of child class.
     */
    final public static function getInstance(): static
    {
        $class = static::class;

        if (App::runningUnitTests() || !isset(self::$_instances[$class])) {
            self::$_instances[$class] = new static();
        }

        return self::$_instances[$class];
    }
}
