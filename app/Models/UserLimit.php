<?php

namespace App\Models;

use Carbon\Carbon;
use Cknow\Money\Money;
use App\Enums\OrderStatus;
use App\Helpers\TaxHelper;
use App\Enums\UserLimitDuration;
use Illuminate\Database\Query\Builder;
use Cknow\Money\Casts\MoneyIntegerCast;
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
        return $this->duration->getLabel();
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

        $carbon_string = Carbon::now()->subDays($this->duration === UserLimitDuration::Daily ? 1 : 7)->toDateTimeString();

        $orders = OrderProduct::toBase()
            ->unless($this->isUnlimited(), fn (Builder $query) => $query->where('orders.created_at', '>=', $carbon_string))
            ->join('orders', 'order_products.order_id', '=', 'orders.id')
            ->where('orders.purchaser_id', $this->user->id)
            ->where('orders.status', '!=', OrderStatus::FullyReturned)
            ->where('order_products.category_id', $this->category->id)
            ->select('order_products.price', 'order_products.quantity', 'order_products.pst', 'order_products.gst', 'order_products.returned')
            ->get()
            ->map(fn ($orderProduct) => TaxHelper::calculateFor(
                Money::parse($orderProduct->price),
                $orderProduct->quantity - $orderProduct->returned,
                $orderProduct->pst !== null,
                [
                    'pst' => $orderProduct->pst,
                    'gst' => $orderProduct->gst,
                ]
            ));

        $activity_registrations = ActivityRegistration::toBase()
            ->unless($this->isUnlimited(), fn (Builder $query) => $query->where('created_at', '>=', $carbon_string))
            ->where('user_id', $this->user->id)
            ->where('category_id', $this->category->id)
            ->where('returned', false)
            ->sum('total_price');

        return $this->_spent = Money::parse(0)->add(...$orders)->add(Money::parse($activity_registrations));
    }
}
