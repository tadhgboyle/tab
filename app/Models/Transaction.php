<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    public const STATUS_NOT_RETURNED = 0;
    public const STATUS_FULLY_RETURNED = 1;
    public const STATUS_PARTIAL_RETURNED = 2;

    use QueryCacheable;
    use HasFactory;

    protected int $cacheFor = 180;

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

    public function purchaser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'purchaser_id');
    }

    public function cashier(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'cashier_id');
    }

    public function rotation(): HasOne
    {
        return $this->hasOne(Rotation::class, 'id', 'rotation_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(TransactionProduct::class);
    }

    public function getCurrentTotal(): float
    {
        if ($this->isReturned()) {
            return 0.00;
        }

        if ($this->getReturnStatus() === self::STATUS_NOT_RETURNED) {
            return $this->total_price;
        }

        return $this->products->sum(function (TransactionProduct $product) {
            return $product->price * ($product->quantity - $product->returned) * $product->getTax();
        });
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
            } else {
                if ($product->returned > 0) {
                    // semi returned if at least one product has a returned value
                    return self::STATUS_PARTIAL_RETURNED;
                }
            }

            $product_count++;
        }

        if ($products_returned >= $product_count) {
            // incase something went wrong and the status wasnt updated earlier, do it now
            $this->update(['returned' => true]);
            return self::STATUS_FULLY_RETURNED;
        }

        if ($products_returned > 0) {
            // will occur if two products are ordered with quantity of 1 and then 1 product is returned but not the other
            return self::STATUS_PARTIAL_RETURNED;
        }

        return self::STATUS_NOT_RETURNED;
    }
}
