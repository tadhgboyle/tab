<?php

namespace App\Models;

use App\Helpers\TaxHelper;
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
        'total_tax',
        'total_price',
        'subtotal',
        'returned',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => MoneyIntegerCast::class,
        'cost' => MoneyIntegerCast::class,
        'gst' => 'float',
        'pst' => 'float',
        'total_tax' => MoneyIntegerCast::class,
        'total_price' => MoneyIntegerCast::class,
        'subtotal' => MoneyIntegerCast::class,
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
            'total_tax' => TaxHelper::calculateTaxFor(
                $productVariant?->price ?? $product->price,
                $quantity,
                $pst !== null,
            ),
            'total_price' => TaxHelper::calculateFor(
                $productVariant?->price ?? $product->price,
                $quantity,
                $pst !== null,
                [
                    'pst' => $pst,
                    'gst' => $gst,
                ],
            ),
            'subtotal' => ($productVariant?->price ?? $product->price)->multiply($quantity),
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
