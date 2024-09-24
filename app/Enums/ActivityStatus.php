<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ActivityStatus implements HasLabel, HasColor
{
    case InProgress;
    case Ended;
    case Upcoming;

    public function getLabel(): string
    {
        return match ($this) {
            self::InProgress => 'In Progress',
            self::Ended => 'Ended',
            self::Upcoming => 'Upcoming',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::InProgress => 'success',
            default => 'gray',
        };
    }
}
