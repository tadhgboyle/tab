<?php

namespace App\Enums;

enum CategoryType: string
{
    case ProductsActivities = 'products_activities';
    case Products = 'products';
    case Activities = 'activities';

    public function getName(): string
    {
        return match ($this) {
            self::ProductsActivities => 'Products & Activities',
            self::Products => 'Products',
            self::Activities => 'Activities',
        };
    }
}
