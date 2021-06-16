<?php

namespace App\Models;

use App\Helpers\ProductHelper;
use Illuminate\Support\Carbon;
use App\Helpers\SettingsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected $cacheFor = 180;

    protected $casts = [
        'name' => 'string',
        'price' => 'float',
        'pst' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean', // stock is never checked
        'stock_override' => 'boolean', // stock can go negative
        'box_size' => 'integer',
    ];

    protected $with = [
        'category',
    ];

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function getPrice(): float
    {
        // TODO: tax calc and implementation
        $total_tax = 0.00;
        if ($this->pst) {
            $total_tax += SettingsHelper::getInstance()->getPst();
        }

        $total_tax += SettingsHelper::getInstance()->getGst();

        $total_tax -= 1;

        return number_format($this->price * $total_tax, 2);
    }

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
            $products = explode(', ', $transaction->products);
            foreach ($products as $transaction_product) {
                if (strtok($transaction_product, '*') != $this->id) {
                    continue;
                }

                $deserialized_product = ProductHelper::deserializeProduct($transaction_product, false);
                $sold += ($deserialized_product['quantity'] - $deserialized_product['returned']);
            }
        }

        return $sold;
    }
}
