<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor
{
    case NotReturned = 'not_returned';
    case PartiallyReturned = 'partially_returned';
    case FullyReturned = 'fully_returned';

    public function getLabel(): string
    {
        return match ($this) {
            self::NotReturned => 'Not Returned',
            self::PartiallyReturned => 'Partially Returned',
            self::FullyReturned => 'Fully Returned',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::NotReturned => 'gray',
            self::PartiallyReturned => 'warning',
            self::FullyReturned => 'danger',
        };
    }
}
