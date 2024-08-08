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
        if ($this->isUnlimited()) {
            $orders = $this->user->orders
                ->where('status', '!=', Order::STATUS_FULLY_RETURNED);
            $activity_registrations = $this->user->activityRegistrations
                ->where('returned', false);
        } else {
            $carbon_string = Carbon::now()->subDays($this->duration === self::LIMIT_DAILY ? 1 : 7)->toDateTimeString();

            $orders = $this->user->orders
                ->where('created_at', '>=', $carbon_string)
                ->where('status', '!=', Order::STATUS_FULLY_RETURNED);

            $activity_registrations = $this->user->activityRegistrations
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);
        }

        $spent = Money::parse(0);

        foreach ($orders as $order) {
            // Loop order products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            foreach ($order->products->filter(fn (OrderProduct $product) => $product->category_id === $this->category->id) as $product) {
                $spent = $spent->add(TaxHelper::forOrderProduct($product, $product->quantity - $product->returned));
            }
        }

        return $spent->add(...$activity_registrations->filter(function (ActivityRegistration $activityRegistration) {
            return $activityRegistration->category_id === $this->category->id;
        })->map->total_price);
    }
}
