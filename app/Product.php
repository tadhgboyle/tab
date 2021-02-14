<?php

namespace App;

use App\Http\Controllers\TransactionController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Product extends Model
{
    
    use QueryCacheable;

    protected $cacheFor = 180;

    protected $casts = [
        'name' => 'string',
        'price' => 'float',
        'pst' => 'boolean',
        'deleted' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean', // stock is never checked
        'stock_override' => 'boolean', // stock can go negative
        'box_size' => 'integer'
    ];

    // Used to check if items in order have enough stock BEFORE using removeStock() to remove it.
    // If we didnt use this, then stock would be adjusted and then the order could fail, resulting in inaccurate stock.
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

    public function removeStock(int $remove_stock): bool
    {
        // Checks 3 things:
        // 1. If the stock is more than we are removing OR -> 2. If the product has unlimited stock => continue
        // 3. If the above fails, if the product has stock override => continue
        if (($this->getStock() >= $remove_stock || $this->unlimited_stock) || $this->stock_override) {
            $this->decrement('stock', $remove_stock);
            return true;
        }

        return false;
    }

    public function adjustStock(int $new_stock)
    {
        return $this->increment('stock', $new_stock);
    }

    public function addBox(int $box_count)
    {
        return $this->adjustStock($box_count * $this->box_size);
    }

    public function findSold(int $stats_time): int
    {
        $sold = 0;

        $transactions = Transaction::where('created_at', '>=', Carbon::now()->subDays($stats_time)->toDateTimeString())->get();
        foreach ($transactions as $transaction) {
            $products = explode(", ", $transaction->products);
            foreach ($products as $transaction_product) {
                if (strtok($transaction_product, "*") != $this->id) {
                    continue;
                }

                $deserialized_product = TransactionController::deserializeProduct($transaction_product, false);
                $sold += ($deserialized_product['quantity'] - $deserialized_product['returned']);
            }
        }

        return $sold;
    }
}
