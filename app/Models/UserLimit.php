<?php

namespace App\Models;

use Carbon\Carbon;
use Cknow\Money\Money;
use App\Enums\OrderStatus;
use App\Helpers\TaxHelper;
use App\Enums\UserLimitDuration;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLimit extends Model
{
    use HasFactory;

    private Money $_spent;

    protected $fillable = [
        'user_id',
        'category_id',
        'limit',
        'duration',
    ];

    protected $casts = [
        'limit' => MoneyIntegerCast::class,
        'duration' => UserLimitDuration::class,
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
        return $this->duration->label();
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

    public function remaining(): Money
    {
        return $this->limit->subtract($this->findSpent());
    }

    public function findSpent(): Money
    {
        if (isset($this->_spent)) {
            return $this->_spent;
        }

        // If they have unlimited money (no limit set) for this category,
        // get all their orders, as they have no limit set we dont need to worry about
        // when the order was created_at.
        $carbon_string = Carbon::now()->subDays($this->duration === UserLimitDuration::Daily ? 1 : 7)->toDateTimeString();

        $orders = $this->user->orders()
            ->with('products')
            ->unless($this->isUnlimited(), fn (Builder $query) => $query->where('created_at', '>=', $carbon_string))
            ->with('products', function ($query) {
                $query->where('category_id', $this->category->id);
            })
            ->where('status', '!=', OrderStatus::FullyReturned)
            ->get();

        $activity_registrations = $this->user->activityRegistrations()
            ->unless($this->isUnlimited(), fn (Builder $query) => $query->where('created_at', '>=', $carbon_string))
            ->where('category_id', $this->category->id)
            ->where('returned', false)
            ->get();

        $spent = $orders
            ->flatMap(fn (Order $order) => $order->products)
            ->reduce(fn (Money $carry, OrderProduct $product) => $carry->add(
                TaxHelper::forOrderProduct($product, $product->quantity - $product->returned)
            ), Money::parse(0));

        return $this->_spent = $spent->add(...$activity_registrations->map->total_price);
    }
}
