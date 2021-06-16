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

        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new static();
        }

        return self::$_instances[$class];
    }

    /**
     * Wipe all instances of helper classes.
     * Used when testing to ensure no data from old tests persists.
     */
    final public static function wipe(): void
    {
        if (App::runningUnitTests()) {
            self::$_instances = [];
        }
    }
}
