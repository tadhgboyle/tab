<?php

namespace App\Models;

use App\Traits\InteractsWithStock;
use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    use InteractsWithStock;

    protected $casts = [
        'name' => 'string',
        'price' => MoneyIntegerCast::class,
        'pst' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean', // stock is never checked
        'stock_override' => 'boolean', // stock can go negative
        'box_size' => 'integer',
        'restore_stock_on_return' => 'boolean',
    ];

    protected $with = [
        'category',
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
