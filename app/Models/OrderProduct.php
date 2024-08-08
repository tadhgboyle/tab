<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
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
    ): OrderProduct {
        return new OrderProduct([
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'quantity' => $quantity,
            'price' => $product->price,
            'gst' => $gst,
            'pst' => $pst,
            'returned' => $returned,
        ]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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
