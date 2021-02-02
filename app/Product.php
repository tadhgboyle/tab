<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Product extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;

    // Used to check if items in order have enough stock BEFORE using removeStock() to remove it.
    // If we didnt use this, then stock would be adjusted and then the order would fail, resulting in inaccurate stock.
    public function hasStock($quantity): bool
    {
        if ($this->unlimited_stock) {
            return true;
        }

        if ($this->stock >= $quantity || $this->stock_override) {
            return true;
        }

        return false;
    }

    public function getStock()
    {
        if ($this->unlimited_stock) {
            return '<i>Unlimited</i>';
        } else {
            return $this->stock;
        }
    }

    public function removeStock($removeStock): bool
    {
        // Checks 3 things:
        // 1. If the stock is more than we are removing OR -> 2. If the product has unlimited stock => continue
        // 3. If the above fails, if the product has stock override => continue
        if (($this->getStock() >= $removeStock || $this->unlimited_stock) || $this->stock_override) {
            $this->decrement('stock', $removeStock);
            return true;
        }

        return false;
    }

    public function adjustStock($new_stock)
    {
        return $this->increment('stock', $new_stock);
    }

    public function addBox($box_count)
    {
        return $this->adjustStock($box_count * $this->box_size);
    }

    public static function findSold($product, $days_ago): int
    {
        $sold = 0;

        foreach (Transaction::where('created_at', '>=', Carbon::now()->subDays($days_ago)->toDateTimeString())->get() as $transaction) {
            foreach (explode(", ", $transaction->products) as $transaction_product) {
                $deserialized_product = OrderController::deserializeProduct($transaction_product);
                if ($deserialized_product['id'] == $product) {
                    $sold += ($deserialized_product['quantity'] - $deserialized_product['returned']);
                }
            }
        }

        return $sold;
    }

}
