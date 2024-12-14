<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderProduct extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'category_id',
        'quantity',
        'price',
        'cost',
        'gst',
        'pst',
        'returned',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => MoneyIntegerCast::class,
        'cost' => MoneyIntegerCast::class,
        'gst' => 'float',
        'pst' => 'float',
        'returned' => 'integer',
    ];

    protected $with = [
        'product',
    ];

    public static function from(
        Product $product,
        ?ProductVariant $productVariant = null,
        int $quantity,
        float $gst,
        ?float $pst = null,
    ): OrderProduct {
        return new OrderProduct([
            'product_id' => $product->id,
            'product_variant_id' => $productVariant?->id,
            'category_id' => $product->category_id,
            'quantity' => $quantity,
            'price' => $productVariant?->price ?? $product->price,
            'cost' => $productVariant?->cost ?? $product->cost,
            'gst' => $gst,
            'pst' => $pst,
            'returned' => 0,
        ]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class)->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
