<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Rennokki\QueryCache\Traits\QueryCacheable;

class Products extends Model
{
    use QueryCacheable;

    protected $cacheFor = 180;

    // Used to check if items in order have enough stock BEFORE using removeStock() to remove it.
    // If we didnt use this, 
    public static function hasStock(array $productIds)
    {
        foreach ($productIds as $productId => $amount) {
            $product = Products::find($productId);
            if ((Products::getStock($productId) >= $amount || $product->unlimited_stock) || $product->stock_override) {
                return 'Not enough of ' . $product->name . ' in stock. Only ' . Products::getStock($productId) . ' remaining.';
            }
        }
        return true;
    }

    public static function getStock($productId)
    {
        $product = Products::find($productId);
        if ($product->unlimited_stock) return 'unlimited';
        else return $product->stock;
    }

    public static function setStock($product, $stock)
    {
        return Products::where('id', $product)->update(['stock' => $stock]);
    }

    public static function removeStock($product, $removeStock)
    {
        // Checks 3 things:
        // 1. If the stock is more than we are removing OF If the product has unlimited stock - continue
        // 3. If the above fails, if the product has stock override - continue
        if ((Products::getStock($product) > $removeStock || $product->unlimited_stock) || $product->stock_override) {
            Products::where('id', $product)->decrement('stock', $removeStock);
            return true;
        }
        return false;
    }

    public static function addStock($product, $newStock)
    {
        return Products::where('id', $product)->increment('stock', $newStock);
    }

    public static function getBoxSize($product)
    {
        return Products::find($product)->box_size;
    }

    public static function setBoxSize($product, $boxSize)
    {
        return Products::where('id', $product)->update(['box_size' => $boxSize]);
    }

    public static function addBox($product, $boxCount)
    {
        return Products::addStock($product, $boxCount * Products::getBoxSize($product));
    }

    public static function isDeleted($product)
    {
        return Products::find($product)->deleted;
    }

    public static function findSold($product, $lookBack)
    {

        $sold = 0;

        foreach (Transactions::where('created_at', '>=', Carbon::now()->subDays($lookBack)->toDateTimeString())->get() as $transaction) {
            foreach (explode(", ", $transaction->products) as $transaction_product) {
                if (OrderController::deserializeProduct($transaction_product)['id'] == $product) $sold++;
            }
        }

        return $sold;
    }
}
