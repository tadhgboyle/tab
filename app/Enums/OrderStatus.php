<?php

namespace App\Enums;

enum OrderStatus: string
{
    case NotReturned = 'not_returned';
    case PartiallyReturned = 'partially_returned';
    case FullyReturned = 'fully_returned';

    public function getWord(): string
    {
        return match ($this) {
            self::NotReturned => 'Not Returned',
            self::PartiallyReturned => 'Partially Returned',
            self::FullyReturned => 'Fully Returned',
        };
    }
}
