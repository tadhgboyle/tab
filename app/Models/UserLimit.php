<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Helpers\TaxHelper;
// use Illuminate\Database\Eloquent\SoftDeletes;

class UserLimit extends Model
{
    public const LIMIT_DAILY = 0;
    public const LIMIT_WEEKLY = 1;

    use HasFactory;
    //use SoftDeletes;


    protected $casts = [
        'limit' => MoneyCast::class,
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
        return Money::parse($this->limit)->equals(Money::parse(-1_00));
    }

    public function canSpend(Money $spending): bool
    {
        if ($this->isUnlimited()) {
            return true;
        }

        return $this->findSpent()->add($spending)->lessThanOrEqual(Money::parse($this->limit));
    }

    public function findSpent(): Money
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($this->isUnlimited()) {
            $transactions = $this->user->transactions
                ->where('status', '!=', Transaction::STATUS_FULLY_RETURNED);
            $activity_registrations = $this->user->activityRegistrations
                ->where('returned', false);
        } else {
            $carbon_string = Carbon::now()->subDays($this->duration === self::LIMIT_DAILY ? 1 : 7)->toDateTimeString();

            $transactions = $this->user->transactions
                ->where('created_at', '>=', $carbon_string)
                ->where('status', '!=', Transaction::STATUS_FULLY_RETURNED);

            $activity_registrations = $this->user->activityRegistrations
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);
        }

        $spent = Money::parse(0);

        foreach ($transactions as $transaction) {
            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            foreach ($transaction->products->filter(fn (TransactionProduct $product) => $product->category_id === $this->category->id) as $product) {
                $spent = $spent->add(TaxHelper::forTransactionProduct($product, $product->quantity - $product->returned));
            }
        }

        return $spent->add(...$activity_registrations->filter(function (ActivityRegistration $activityRegistration) {
            return $activityRegistration->category_id === $this->category->id;
        })->map->total_price->map(function ($price) {
            return Money::parse($price);
        }));
    }
}
