<?php

namespace App\Filament\Widgets;

use App\Helpers\RotationHelper;
use App\Models\Activity;
use App\Models\ActivityRegistration;
use App\Models\GiftCard;
use App\Models\Transaction;
use Cknow\Money\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Rotation;

class ActivityOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Activity Revenue', Money::parse(ActivityRegistration::query()->sum('activity_price'))),
            Stat::make(
                'Activity Slot Utilization',
                number_format(Activity::query()->sum('slots') / ActivityRegistration::query()->count(), 2) . '%'
            )->description(
                ActivityRegistration::query()->count() . '/' . Activity::query()->sum('slots') . ' slots filled'
            ),
        ];
    }
}
