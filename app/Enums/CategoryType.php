<?php

namespace App\Enums;

enum CategoryType: int
{
    case ProductsActivities = 1;
    case Products = 2;
    case Activities = 3;

    public function getName(): string
    {
        return match ($this) {
            self::ProductsActivities => 'Products & Activities',
            self::Products => 'Products',
            self::Activities => 'Activities',
        };
    }
}
