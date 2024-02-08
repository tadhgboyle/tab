<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Transaction;
use Cknow\Money\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Orders', Transaction::count()),
            Stat::make('Average Order Value', Money::parse(Transaction::average('total_price') / 100)),
            //Stat::make('Average User Orders', Transaction::average('id')->average('id')),
        ];
    }
}
