<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProductStatus: string implements HasLabel, HasColor
{
    case Active = 'active';

    case Draft = 'draft';

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Draft => 'Draft',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Draft => 'gray',
        };
    }
}
