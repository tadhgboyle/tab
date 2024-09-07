<?php

namespace App\Enums;

enum UserLimitDuration: int
{
    case Daily = 0;
    case Weekly = 1;

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'day',
            self::Weekly => 'week',
        };
    }
}
