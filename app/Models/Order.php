<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Cknow\Money\Money;
use App\Concerns\Timeline\HasTimeline;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\Timeline\TimelineEntry;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model implements HasTimeline
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    protected $casts = [
        'status' => OrderStatus::class,
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

    // TODO: make this a column in the database along with total_tax = total_price
    // TODO: test
    public function subtotal(): Money
    {
        $subtotal = Money::parse(0);

        foreach ($this->products as $product) {
            $subtotal = $subtotal->add($product->price->multiply($product->quantity));
        }

        return $subtotal;
    }

    // TODO: test
    public function totalTax(): Money
    {
        return $this->total_price->subtract($this->subtotal());
    }

    public function getReturnedTotal(): Money
    {
        if ($this->isReturned()) {
            return $this->total_price;
        }

        if ($this->status === OrderStatus::NotReturned) {
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

        if ($this->status === OrderStatus::NotReturned) {
            return Money::parse(0);
        }

        return Money::sum(Money::parse(0), ...$this->productReturns->map->purchaser_amount);
    }

    public function isReturned(): bool
    {
        return $this->status === OrderStatus::FullyReturned;
    }

    public function getStatusHtml(): string
    {
        return match ($this->status) {
            OrderStatus::FullyReturned => '<span class="tag is-medium">🚨 Returned</span>',
            OrderStatus::PartiallyReturned => '<span class="tag is-medium">⚠️ Partially Returned</span>',
            OrderStatus::NotReturned => '<span class="tag is-medium">👌 Not Returned</span>',
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
        foreach ($this->productReturns()->with('orderProduct')->get() as $productReturn) {
            $description = "Returned {$productReturn->orderProduct->product->name}";
            if ($hasCashReturn = $productReturn->purchaser_amount->isPositive()) {
                $description .= " in cash as {$productReturn->purchaser_amount}";
            }
            if ($productReturn->gift_card_amount->isPositive()) {
                $description .= ($hasCashReturn ? ' and ' : '') . " {$productReturn->gift_card_amount} to gift card";
            }
            $events[] = new TimelineEntry(
                description: $description,
                emoji: '🔄',
                time: $productReturn->created_at,
                actor: $productReturn->returner,
            );
        }

        if ($this->isReturned()) {
            $orderReturn = $this->return;
            $hasProductReturn = $this->productReturns->isNotEmpty();

            $description = 'Fully returned';
            if (!$orderReturn->caused_by_product_return) {
                if ($hasCashReturn = $orderReturn->purchaser_amount->isPositive()) {
                    $description .= ($hasProductReturn ? ' remaining ' : '') . " in cash as {$orderReturn->purchaser_amount}";
                }
                if ($orderReturn->gift_card_amount->isPositive()) {
                    $description .= ($hasCashReturn ? ' and ' : ($hasProductReturn ? ' remaining ' : '')) . " {$orderReturn->gift_card_amount} to gift card";
                }
            } else {
                $description .= ' due to product return';
            }

            $events[] = new TimelineEntry(
                description: $description,
                emoji: '🔄',
                time: $orderReturn->created_at,
                actor: $orderReturn->returner,
            );
        }

        usort($events, fn ($a, $b) => $a->time <=> $b->time);

        return array_merge($timeline, $events);
    }
}
