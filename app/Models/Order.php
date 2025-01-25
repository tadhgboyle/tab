<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Enums\OrderStatus;
use App\Helpers\SettingsHelper;
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
        'total_tax' => MoneyIntegerCast::class,
        'subtotal' => MoneyIntegerCast::class,
        'purchaser_amount' => MoneyIntegerCast::class,
        'gift_card_amount' => MoneyIntegerCast::class,
    ];

    public static function boot(): void
    {
        parent::boot();

        static::created(function (Order $order) {
            $prefix = app(SettingsHelper::class)->getOrderPrefix();
            $suffix = app(SettingsHelper::class)->getOrderSuffix();

            $order->identifier = "{$prefix}{$order->id}{$suffix}";
            $order->save();
        });
    }

    public function purchaser(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
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

        if ($this->status === OrderStatus::NotReturned) {
            return Money::parse(0);
        }

        return Money::sum(Money::parse(0), ...$this->productReturns->map->total_return_amount);
    }

    public function getOwingTotal(): Money
    {
        return $this->total_price->subtract($this->getReturnedTotal());
    }

    public function getPurchaserOwingTotal(): Money
    {
        return $this->purchaser_amount->subtract($this->getReturnedTotalToCash());
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

    public function getReturnedTotalToCash(): Money
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

    public function totalCost(): Money
    {
        return Money::sum(...$this->products->map(fn (OrderProduct $product) => $product->cost->multiply($product->quantity)));
    }

    public function totalMargin(): Money
    {
        return $this->subtotal->subtract($this->totalCost());
    }
    
    public function marginPercentage(): float
    {
        if ($this->subtotal->isZero()) {
            return 0;
        }

        return round($this->totalMargin()->getAmount() / $this->subtotal->getAmount() * 100, 2);
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
        foreach ($this->productReturns()->with('returner', 'orderProduct')->get() as $productReturn) {
            $productName = $productReturn->orderProduct->productVariant
                ? $productReturn->orderProduct->productVariant->description()
                : $productReturn->orderProduct->product->name;
            $description = "Returned {$productName}";
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
