<?php

namespace App\Models;

use App\Traits\InteractsWithStock;
use Illuminate\Support\Collection;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use SoftDeletes;
    use InteractsWithStock;

    protected $casts = [
        'stock' => 'integer',
        'price' => MoneyIntegerCast::class,
    ];

    protected $fillable = ['sku', 'price', 'stock', 'box_size'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValueAssignments(): HasMany
    {
        return $this->hasMany(ProductVariantOptionValueAssignment::class);
    }

    public function description(): string
    {
        return $this->product->name . ': ' . $this->descriptions(true)->implode(', ');
    }

    /**
     * Get a collection of descriptions for the variant, IE: Color: Red, Size: Large
     * @param bool $excludeTrashedOptions Whether or not to exclude trashed options, `true` for cashier page, `false` when viewing historic orders
     */
    public function descriptions(bool $excludeTrashedOptions): Collection
    {
        $this->loadMissing('optionValueAssignments.productVariantOption', 'optionValueAssignments.productVariantOptionValue');

        return $this->optionValueAssignments->filter(function (ProductVariantOptionValueAssignment $assignment) use ($excludeTrashedOptions) {
            if ($excludeTrashedOptions) {
                return !$assignment->productVariantOption->trashed();
            }

            return true;
        })->map(function (ProductVariantOptionValueAssignment $assignment) {
            return $assignment->productVariantOption->name . ': ' . $assignment->productVariantOptionValue->value;
        });
    }

    public function getStockOverrideAttribute(): bool
    {
        return $this->product->stock_override;
    }

    public function getUnlimitedStockAttribute(): bool
    {
        return $this->product->unlimited_stock;
    }
}
