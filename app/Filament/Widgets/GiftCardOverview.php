<?php

namespace App\Filament\Widgets;

use App\Helpers\RotationHelper;
use App\Models\GiftCard;
use App\Models\Transaction;
use Cknow\Money\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use App\Models\Rotation;

class GiftCardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $total_gift_card_usage = Money::parse(Transaction::query()->whereNot('gift_card_id', null)->sum('gift_card_amount'));
        $total_gift_card_original = Money::parse(GiftCard::query()->sum('original_balance'));
        $total_gift_card_remaining = Money::parse(GiftCard::query()->sum('remaining_balance'));
        $utilization_percent = $total_gift_card_usage->getAmount() / ($total_gift_card_usage->getAmount() + $total_gift_card_remaining->getAmount()) * 100;
        $utilization_percent = number_format($utilization_percent, 2) . '%';
        $total_revenue_percent = Transaction::query()->whereNot('gift_card_id', null)->sum('gift_card_amount') / Transaction::query()->sum('total_price') * 100;
        $total_revenue_percent = number_format($total_revenue_percent, 2) . '%';

        return [
            Stat::make('Gift Card Revenue', Money::parse(Transaction::query()->whereNot('gift_card_id', null)->sum('gift_card_amount')))
                ->description("{$total_revenue_percent} of total revenue"),
            Stat::make('Gift Card Utilization', $utilization_percent)
                ->description(
                    "{$total_gift_card_usage}/{$total_gift_card_original} used"
                ),
            Stat::make('Average Gift Card Usage', $total_gift_card_usage->divide(GiftCard::query()->count())),
        ];
    }
}
