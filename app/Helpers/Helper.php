<?php

namespace App\Helpers;

abstract class Helper
{
    private static $_instances = [];

    final public static function getInstance(bool $new = false): static
    {
        $class = static::class;

        if ($new || !isset(self::$_instances[$class])) {
            self::$_instances[$class] = new static();
        }

        return self::$_instances[$class];
    }
}
