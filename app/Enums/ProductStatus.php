<?php

namespace App\Enums;

enum ProductStatus: int
{
    case Active = 1;

    case Draft = 0;

    public function getWord(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Draft => 'Draft',
        };
    }
}
