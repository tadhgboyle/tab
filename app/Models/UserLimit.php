<?php

namespace App\Models;

use Carbon\Carbon;
use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLimit extends Model
{
    public const LIMIT_DAILY = 0;
    public const LIMIT_WEEKLY = 1;

    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'limit',
        'duration',
    ];

    protected $casts = [
        'limit' => MoneyIntegerCast::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function duration(): string
    {
        return $this->duration === self::LIMIT_DAILY ? 'day' : 'week';
    }

    public function isUnlimited(): bool
    {
        return $this->limit->equals(Money::parse(-1_00));
    }

    public function canSpend(Money $spending): bool
    {
        if ($this->isUnlimited()) {
            return true;
        }

        return $this->findSpent()->add($spending)->lessThanOrEqual($this->limit);
    }

    public function findSpent(): Money
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their orders, as they have no limit set we dont need to worry about
        // when the order was created_at.
        if (!$this->isUnlimited()) {
            $carbon_string = Carbon::now()->subDays($this->duration === self::LIMIT_DAILY ? 1 : 7)->toDateTimeString();
        }

        $orders = $this->user->orders()
            ->with('products')
            ->unless($this->isUnlimited(), fn ($query) => $query->where('created_at', '>=', $carbon_string))
            ->with('products', function ($query) {
                $query->where('category_id', $this->category->id);
            })
            ->where('status', '!=', Order::STATUS_FULLY_RETURNED)
            ->get();

        $activity_registrations = $this->user->activityRegistrations()
            ->unless($this->isUnlimited(), fn ($query) => $query->where('created_at', '>=', $carbon_string))
            ->where('category_id', $this->category->id)
            ->where('returned', false)
            ->get();

        $spent = $orders
            ->flatMap(fn (Order $order) => $order->products)
            ->reduce(fn (Money $carry, OrderProduct $product) => $carry->add(
                TaxHelper::forOrderProduct($product, $product->quantity - $product->returned)
            ), Money::parse(0));

        return $spent->add(...$activity_registrations->map->total_price);
    }
}
