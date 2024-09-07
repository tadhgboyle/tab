<?php

namespace App\Enums;

enum OrderStatus: int
{
    case NotReturned = 0;
    case PartiallyReturned = 1;
    case FullyReturned = 2;

    public function getWord(): string
    {
        return match ($this) {
            self::NotReturned => 'Not Returned',
            self::PartiallyReturned => 'Partially Returned',
            self::FullyReturned => 'Fully Returned',
        };
    }
}
