<?php

namespace App\Helpers;

abstract class Helper
{
    private static Helper $_instance;

    public static final function getInstance(): static
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new static;
        }

        return self::$_instance;
    }
}