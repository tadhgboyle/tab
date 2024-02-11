<?php

namespace App\Filament\Widgets;

use App\Helpers\RotationHelper;
use App\Models\Transaction;
use Cknow\Money\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Rotation;

class OrderOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            $this->totalRevenueStat(),
            $this->totalOrdersStat(),
            $this->ordersThisRotationStat(),
            $this->averageOrderValueStat(),
        ];
    }

    private function totalRevenueStat(): Stat
    {
        $revenueTrend = Trend::model(Transaction::class)
            ->between(
                start: now()->subWeek(),
                end: now(),
            )
            ->perDay()
            ->sum('total_price');

        $most_recent = $revenueTrend->last()->aggregate / 100;
        $second_last = $revenueTrend->reverse()->values()->get(1)->aggregate / 100;

        $difference = $most_recent - $second_last;
        $difference_money = Money::parse($difference);
        $description = $difference > 0 ? "{$difference_money} increase DoD" : "{$difference_money} decrease DoD";
        $icon = $difference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $difference > 0 ? 'success' : 'danger';

        return Stat::make('Total Revenue', Money::sum(...$revenueTrend->map(fn (TrendValue $value) => Money::parse($value->aggregate))))
            ->description($description)
            ->descriptionIcon($icon)
            ->descriptionColor($color)
            ->chart($revenueTrend->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->chartColor($color);
    }

    private function totalOrdersStat(): Stat
    {
        $orders = Trend::model(Transaction::class)
            ->between(
                start: now()->subWeek(),
                end: now(),
            )
            ->perDay()
            ->count();
        $color = $orders->last()?->aggregate > $orders->first()?->aggregate ? 'success' : 'danger';

        return Stat::make('Total Orders', Transaction::count())
            ->chart($orders->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->chartColor($color);
    }

    private function ordersThisRotationStat(): ?Stat
    {
        $currentRotation = app(RotationHelper::class)->getCurrentRotation();
        if (!$currentRotation) {
            return Stat::make('Orders this Rotation', null)
                ->description('No rotation currently active')
                ->color('danger');
        }

        $ordersCurrentRotationTrend = Trend::model(Transaction::class)
            ->between(
                start: $currentRotation->start,
                end: $currentRotation->end,
            )
            ->perDay()
            ->count()
            ->reverse();
        $ordersCurrentRotation = Transaction::where('rotation_id', $currentRotation->id)->count();
        $color = $ordersCurrentRotationTrend->last()?->aggregate > $ordersCurrentRotationTrend->first()?->aggregate ? 'success' : 'danger';

        $lastRotation = app(RotationHelper::class)->getRotations()->filter(fn (Rotation $rotation) => $rotation->end->isPast())->sortByDesc('end')->first();

        if (!$lastRotation) {
            $description = null;
            $icon = null;
            $color = null;
        } else {
            $count = Transaction::where('rotation_id', $lastRotation->id)->count();
            $increase = $ordersCurrentRotation - $count;
            $description = $increase > 0 ? "{$increase} increase" : "{$increase} decrease";
            $icon = $increase > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
            $color = $increase > 0 ? 'success' : 'danger';
        }

        return Stat::make('Orders this Rotation', $ordersCurrentRotation)
            ->description($description)
            ->descriptionIcon($icon)
            ->descriptionColor($color)
            ->chart($ordersCurrentRotationTrend->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->chartColor($color);
    }

    private function averageOrderValueStat(): Stat
    {
        $averages = Trend::model(Transaction::class)
            ->between(
                start: now()->subWeek(),
                end: now(),
            )
            ->perDay()
            ->average('total_price');

        // get second last most recent average
        $most_recent = $averages->last()->aggregate / 100;
        $second_last = $averages->reverse()->values()->get(1)->aggregate / 100;

        $difference = $most_recent - $second_last;
        $difference_money = Money::parse($difference);
        $description = $difference > 0 ? "{$difference_money} increase DoD" : "{$difference_money} decrease DoD";
        $icon = $difference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $color = $difference > 0 ? 'success' : 'danger';

        return Stat::make('Average Order Value', Money::parse($most_recent))
            ->description($description)
            ->descriptionIcon($icon)
            ->descriptionColor($color)
            ->chart($averages->map(fn (TrendValue $value) => $value->aggregate)->toArray())
            ->chartColor($color);
    }
}
