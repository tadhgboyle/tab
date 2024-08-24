<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use App\Traits\InteractsWithStock;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    use InteractsWithStock;

    protected $casts = [
        'name' => 'string',
        'price' => MoneyIntegerCast::class,
        'pst' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean', 
        'stock_override' => 'boolean',
        'box_size' => 'integer',
        'restore_stock_on_return' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'sku',
        'price',
        'category_id',
        'stock',
        'box_size',
        'unlimited_stock',
        'stock_override',
        'pst',
        'restore_stock_on_return',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantOptions(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function hasAllVariantCombinations(): bool
    {
        return $this->variants->count() === $this->variantOptions->reduce(function ($carry, ProductVariantOption $option) {
            return $carry * $option->values->count();
        }, 1);
    }

    // TODO: could this be getPriceAttribute()?
    public function getVariantPriceRange(): string
    {
        if ($this->hasVariants()) {
            $min = $this->variants->min('price');
            $max = $this->variants->max('price');

            if ($min === $max) {
                return $min;
            }

            return "{$min} - {$max}";
        }

        return $this->price;
    }

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    public function getStockAttribute(int $baseStock): int
    {
        if ($this->hasVariants()) {
            return $this->variants->sum('stock');
        }

        return $baseStock;
    }
}
