<?php

namespace App\Enums;

use App\Models\Transaction;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: int implements HasColor, HasIcon, HasLabel
{
    case Normal = Transaction::STATUS_NORMAL;

    case PartiallyReturned = Transaction::STATUS_PARTIAL_RETURNED;

    case FullyReturned = Transaction::STATUS_FULLY_RETURNED;

    public function getLabel(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::PartiallyReturned => 'Partially Returned',
            self::FullyReturned => 'Fully Returned',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Normal => 'info',
            self::PartiallyReturned => 'warning',
            self::FullyReturned => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Normal => 'heroicon-m-sparkles',
            self::PartiallyReturned => 'heroicon-m-arrow-path',
            self::FullyReturned => 'heroicon-m-x-circle',
        };
    }
}
