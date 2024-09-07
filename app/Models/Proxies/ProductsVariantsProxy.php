<?php

namespace App\Models\Proxies;

use Sushi\Sushi;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductsVariantsProxy extends Model
{
    use Sushi;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getRows(): array
    {
        return Product::query()
        ->with('category', 'variants', 'variantOptions', 'variants.product')
        ->get()
        ->flatMap(function (Product $product) {
            if ($product->hasVariants()) {
                return $product->variants->map(function (ProductVariant $variant) use ($product) {
                    return [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'name' => $variant->description(),
                        'sku' => $variant->sku,
                        'category_id' => $product->category->id,
                        'category_name' => $product->category->name,
                        'stock' => $variant->stock,
                        'stock_override' => $product->stock_override,
                        'box_size' => $variant->box_size ?: 'N/A',
                    ];
                });
            }

            return [
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category_id' => $product->category->id,
                    'category_name' => $product->category->name,
                    'stock' => $product->stock,
                    'stock_override' => $product->stock_override,
                    'box_size' => $product->box_size === -1 ? 'N/A' : $product->box_size,
                ]
            ];
        })->toArray();
    }
}
