<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionProduct extends Model
{
    protected $casts = [
        'quantity' => 'integer',
        'price' => MoneyCast::class,
        'gst' => 'float',
        'pst' => 'float',
        'returned' => 'integer',
    ];

    protected $with = [
        'product',
    ];

    public static function from(
        Product $product,
        int $quantity,
        float $gst,
        ?float $pst = null,
        int $returned = 0
    ): TransactionProduct {
        return new TransactionProduct([
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'quantity' => $quantity,
            'price' => $product->price,
            'gst' => $gst,
            'pst' => $pst,
            'returned' => $returned,
        ]);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
