<?php

namespace App\Models;

use App\Helpers\TaxHelper;
use App\Traits\InteractsWithStock;
use Cknow\Money\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
        'box_size' => 'integer',
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

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->product->pst);
    }

    public function description(): string
    {
        return $this->product->name . ': ' . $this->descriptions(true)->implode(', ');
    }

    /**
     * Get a collection of descriptions for the variant, IE: Color: Red, Size: Large.
     *
     * @param bool $excludeTrashedOptions Whether or not to exclude trashed options, `true` for cashier page, `false` when viewing historic orders
     */
    public function descriptions(bool $excludeTrashedOptions): Collection
    {
        dd(DB::select('SELECT sqlite_version();'));
        $results = DB::select("
            SELECT 
                CONCAT(o.name, ': ', v.value) AS option_value_assignment
            FROM product_variant_option_value_assignments a
            JOIN product_variant_options o ON a.product_variant_option_id = o.id
            JOIN product_variant_option_values v ON a.product_variant_option_value_id = v.id
            WHERE 
                (:exclude_trashed_options = 0 OR (o.deleted_at IS NULL AND v.deleted_at IS NULL))
                AND a.product_variant_id = :product_variant_id
            ", [
            'exclude_trashed_options' => $excludeTrashedOptions,
            'product_variant_id' => $this->id,
        ]);

        return collect($results)->pluck('option_value_assignment');
    }

    public function optionValueFor(ProductVariantOption $option): ?ProductVariantOptionValue
    {
        $assignment = $this->optionValueAssignments->where('product_variant_option_id', $option->id)->first();
        if (!$assignment) {
            return null;
        }

        if ($assignment->productVariantOption->trashed()) {
            return null;
        }

        if ($assignment->productVariantOptionValue->trashed()) {
            return null;
        }

        return $assignment->productVariantOptionValue;
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
