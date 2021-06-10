<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Rennokki\QueryCache\Traits\QueryCacheable;
use App\Http\Controllers\TransactionController;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** @method static Transaction find(int $id) */
class Transaction extends Model
{
    public const STATUS_NOT_RETURNED = 0;
    public const STATUS_FULLY_RETURNED = 1;
    public const STATUS_PARTIAL_RETURNED = 2;
    
    use QueryCacheable;
    use HasFactory;

    protected $cacheFor = 180;

    protected $fillable = [
        'products', // TODO: can we auto explode(',') this?
        'status',
    ];

    protected $casts = [
        'status' => 'boolean', // TODO: Rename this to "returned"
    ];

    protected $with = [
        'purchaser',
        'cashier',
    ];

    public function purchaser()
    {
        return $this->hasOne(User::class, 'id', 'purchaser_id');
    }

    public function cashier()
    {
        return $this->hasOne(User::class, 'id', 'cashier_id');
    }

    public function isReturned(): bool
    {
        return $this->getReturnStatus() === self::STATUS_FULLY_RETURNED;
    }

    public function getReturnStatus(): int
    {
        if ($this->status) {
            return self::STATUS_FULLY_RETURNED;
        }

        $products_returned = 0;
        $product_count = 0;

        $products = explode(', ', $this->products);
        foreach ($products as $product) {
            $product_info = TransactionController::deserializeProduct($product, false);
            if ($product_info['returned'] >= $product_info['quantity']) {
                $products_returned++;
            } else {
                if ($product_info['returned'] > 0) {
                    // semi returned if at least one product has a returned value
                    return self::STATUS_PARTIAL_RETURNED;
                }
            }

            $product_count++;
        }

        if ($products_returned >= $product_count) {
            // incase something went wrong and the status wasnt updated earlier, do it now
            $this->update(['status' => true]);
            return self::STATUS_FULLY_RETURNED;
        }

        if ($products_returned > 0) {
            // will occur if two products are ordered with quantity of 1 and then 1 product is returned but not the other
            // TODO: can probably add this logic above
            return self::STATUS_PARTIAL_RETURNED;
        }

        return self::STATUS_NOT_RETURNED;
    }
}
