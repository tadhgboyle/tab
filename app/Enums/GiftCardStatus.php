<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GiftCardStatus implements HasLabel, HasColor
{
    case Active;
    case Expired;

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Expired => 'Expired',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'danger',
        };
    }
}
