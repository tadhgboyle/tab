<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    public const STATUS_NOT_RETURNED = 0;
    public const STATUS_FULLY_RETURNED = 1;
    public const STATUS_PARTIAL_RETURNED = 2;

    use HasFactory;

    protected $fillable = [
        'returned',
    ];

    protected $casts = [
        'returned' => 'boolean',
    ];

    protected $with = [
        'purchaser',
        'cashier',
        'products',
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
        return $this->hasMany(TransactionProduct::class);
    }

    public function getReturnedTotal(): float
    {
        if ($this->isReturned()) {
            return $this->total_price;
        }

        if ($this->getReturnStatus() === self::STATUS_NOT_RETURNED) {
            return 0.00;
        }

        return $this->products->where('returned', '>=', 1)->sum(function (TransactionProduct $product) {
            return $product->price * $product->returned * $product->getTax();
        });
    }

    public function isReturned(): bool
    {
        return $this->getReturnStatus() === self::STATUS_FULLY_RETURNED;
    }

    public function getReturnStatus(): int
    {
        if ($this->returned) {
            return self::STATUS_FULLY_RETURNED;
        }

        $products_returned = 0;
        $product_count = 0;

        foreach ($this->products as $product) {
            if ($product->returned >= $product->quantity) {
                $products_returned++;
            } else if ($product->returned > 0) {
                // Semi returned if at least one product has a returned value
                return self::STATUS_PARTIAL_RETURNED;
            }

            $product_count++;
        }

        if ($products_returned >= $product_count) {
            // In case something went wrong and the status wasn't updated earlier, do it now
            $this->update(['returned' => true]);
            return self::STATUS_FULLY_RETURNED;
        }

        if ($products_returned > 0) {
            // Will occur if two products are ordered with quantity of 1 and then 1 product is returned but not the other
            return self::STATUS_PARTIAL_RETURNED;
        }

        return self::STATUS_NOT_RETURNED;
    }
}
