<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionProduct extends Model
{
    protected $fillable = [
        'product_id',
        'category_id',
        'quantity',
        'price',
        'gst',
        'pst',
        'returned',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => MoneyIntegerCast::class,
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

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
}
