<?php

namespace App\Enums;

enum UserLimitDuration: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::Daily => 'day',
            self::Weekly => 'week',
        };
    }
}
