<?php

namespace App\Helpers;

abstract class Helper
{
    private static $_instances = [];

    final public static function getInstance(): static
    {
        $class = static::class;

        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new static();
        }

        return self::$_instances[$class];
    }
}
