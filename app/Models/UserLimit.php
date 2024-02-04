<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use App\Helpers\TaxHelper;

class UserLimit extends Model
{
    public const LIMIT_DAILY = 0;
    public const LIMIT_WEEKLY = 1;

    use HasFactory;

    protected $primaryKey = 'limit_id';

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

    public function canSpend(Money $spending): bool
    {
        if ($this->limit->equals(Money::parse(-1_00))) {
            return true;
        }

        return $this->findSpent()->add($spending)->lessThanOrEqual($this->limit);
    }

    public function findSpent(): Money
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($this->limit->equals(Money::parse(-1_00))) {
            $transactions = $this->user->transactions
                ->where('returned', false);
            $activity_registrations = $this->user->activityRegistrations
                ->where('returned', false);
        } else {
            $carbon_string = Carbon::now()->subDays($this->duration === self::LIMIT_DAILY ? 1 : 7)->toDateTimeString();

            $transactions = $this->user->transactions
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);

            $activity_registrations = $this->user->activityRegistrations
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);
        }

        $spent = Money::parse(0);

        foreach ($transactions as $transaction) {
            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            foreach ($transaction->products->filter(fn (TransactionProduct $product) => $product->category_id === $this->category->id) as $product) {
                $quantity_available = $product->quantity - $product->returned;

                $spent = $spent->add(TaxHelper::calculateFor($product->price, $quantity_available, $product->pst !== null, [
                    'gst' => $product->gst,
                    'pst' => $product->pst,
                ]));
            }
        }

        return $spent->add(...$activity_registrations->filter(function (ActivityRegistration $activityRegistration) {
            return $activityRegistration->category_id === $this->category->id;
        })->map->total_price);
    }
}
