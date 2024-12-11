<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum UserLimitDuration: string implements HasLabel
{
    case Daily = 'daily';
    case Weekly = 'weekly';

    public function getLabel(): string
    {
        return match ($this) {
            self::Daily => 'Daily',
            self::Weekly => 'Weekly',
        };
    }
}
