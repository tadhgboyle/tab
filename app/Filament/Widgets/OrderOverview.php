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
            $this->totalOrdersStat(),
            $this->ordersThisRotationStat(),
            $this->averageOrderValueStat(),
        ];
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
            ->count();
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
        $averageOrderValue = Money::parse(Transaction::average('total_price'))->divide(100);
        $color = $averageOrderValue->greaterThan(Money::parse(100)) ? 'success' : 'danger';

        return Stat::make('Average Order Value', $averageOrderValue)
            ->chart(Transaction::select(
                \DB::raw('DATE(created_at) as day'),
                \DB::raw('AVG(total_price) as average_price')
            )
            ->groupBy(\DB::raw('DATE(created_at)'))
            ->orderBy('day', 'desc')
            ->get()
            ->map(fn ($result) => $result->average_price / 100)
            ->toArray())
            ->chartColor($color);
    }
}
