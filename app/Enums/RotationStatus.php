<?php

namespace App\Enums;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RotationStatus implements HasLabel, HasColor
{
    case Past;
    case Present;
    case Future;

    public function getLabel(): string
    {
        return match ($this) {
            self::Past => 'Past',
            self::Present => 'Present',
            self::Future => 'Future',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Present => 'success',
            default => 'gray',
        };
    }
}
