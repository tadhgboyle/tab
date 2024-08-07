<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Timeline\TimelineEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model implements HasTimeline
{
    public const STATUS_NOT_RETURNED = 0;

    public const STATUS_PARTIAL_RETURNED = 1;

    public const STATUS_FULLY_RETURNED = 2;

    use HasFactory;

    protected $fillable = [
        'status',
    ];

    protected $casts = [
        'total_price' => MoneyIntegerCast::class,
        'purchaser_amount' => MoneyIntegerCast::class,
        'gift_card_amount' => MoneyIntegerCast::class,
    ];

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rotation(): BelongsTo
    {
        return $this->belongsTo(Rotation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function return(): HasOne
    {
        return $this->hasOne(OrderReturn::class);
    }

    public function productReturns(): HasMany
    {
        return $this->hasMany(OrderProductReturn::class);
    }

    public function getReturnedTotal(): Money
    {
        if ($this->isReturned()) {
            return $this->total_price;
        }

        if ($this->status === self::STATUS_NOT_RETURNED) {
            return Money::parse(0);
        }

        return Money::sum(Money::parse(0), ...$this->productReturns->map->total_return_amount);
    }

    public function getOwingTotal(): Money
    {
        return $this->total_price->subtract($this->getReturnedTotal());
    }

    public function getReturnedTotalToGiftCard(): Money
    {
        if ($this->isReturned()) {
            return $this->gift_card_amount;
        }

        if ($this->gift_card_amount->isZero()) {
            return Money::parse(0);
        }

        return Money::sum(Money::parse(0), ...$this->productReturns->map->gift_card_amount);
    }

    public function getReturnedTotalInCash(): Money
    {
        if ($this->isReturned()) {
            return $this->purchaser_amount;
        }

        if ($this->status === self::STATUS_NOT_RETURNED) {
            return Money::parse(0);
        }

        return Money::sum(Money::parse(0), ...$this->productReturns->map->purchaser_amount);
    }

    public function isReturned(): bool
    {
        return $this->status === self::STATUS_FULLY_RETURNED;
    }

    public function getStatusHtml(): string
    {
        return match ($this->status) {
            self::STATUS_FULLY_RETURNED => '<span class="tag is-medium">🚨 Returned</span>',
            self::STATUS_PARTIAL_RETURNED => '<span class="tag is-medium">⚠️ Semi Returned</span>',
            self::STATUS_NOT_RETURNED => '<span class="tag is-medium">👌 Not Returned</span>',
        };
    }

    public function timeline(): array
    {
        $timeline = [
            new TimelineEntry(
                description: 'Created',
                emoji: '🛒',
                time: $this->created_at,
                actor: $this->cashier,
            ),
        ];

        $events = [];
        foreach ($this->productReturns as $productReturn) {
            $description = "Returned {$productReturn->orderProduct->product->name}";
            if ($hasCashReturn = $productReturn->purchaser_amount->isPositive()) {
                $description .= " in cash as {$productReturn->purchaser_amount}";
            }
            if ($productReturn->gift_card_amount->isPositive()) {
                $description .= ($hasCashReturn ? " and " : "" ). " {$productReturn->gift_card_amount} to gift card";
            }
            $events[] = new TimelineEntry(
                description: $description,
                emoji: '🔄',
                time: $productReturn->created_at,
                actor: $productReturn->returner,
            );
        }

        if ($this->isReturned()) {
            $description = 'Fully returned';
            if ($this->return->caused_by_product_return) {
                $description .= ' due to product return';
            }

            $events[] = new TimelineEntry(
                description: $description,
                emoji: '🔄',
                time: $this->return->created_at,
                actor: $this->return->returner,
            );
        }

        usort($events, fn ($a, $b) => $a->time <=> $b->time);

        return array_merge($timeline, $events);
    }
}
