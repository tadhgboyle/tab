<?php

namespace App\Models;

use App\Helpers\ProductHelper;
use App\Helpers\SettingsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use QueryCacheable;
    use HasFactory;
    use SoftDeletes;

    protected int $cacheFor = 180;

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

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function getPrice(): float
    {
        // TODO: tax calc and implementation
        $total_tax = 0.00;
        if ($this->pst) {
            $total_tax += resolve(SettingsHelper::class)->getPst();
        }

        $total_tax += resolve(SettingsHelper::class)->getGst();

        --$total_tax;

        return (float) number_format($this->price * $total_tax, 2);
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
        }

        return $this->stock;
    }

    public function removeStock(int $remove_stock): bool
    {
        if ($this->stock_override || ($this->unlimited_stock || $this->getStock() >= $remove_stock)) {
            $this->decrement('stock', $remove_stock);
            return true;
        }

        return false;
    }

    public function adjustStock(int $new_stock): bool|int
    {
        return $this->increment('stock', $new_stock);
    }

    public function addBox(int $box_count): bool|int
    {
        return $this->adjustStock($box_count * $this->box_size);
    }

    public function findSold(int $rotation_id): int
    {
        $sold = 0;

        $transactions = Transaction::where('rotation_id', $rotation_id)->get();
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
