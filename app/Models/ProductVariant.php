<?php

namespace App\Models;

use App\Traits\InteractsWithStock;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class ProductVariant extends Model
{
    use SoftDeletes;
    use InteractsWithStock;

    protected $casts = [
        'stock' => 'integer',
        'price' => MoneyIntegerCast::class,
    ];

    protected $fillable = ['sku', 'price', 'stock'];

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
        return $this->product->name . ': ' . $this->descriptions()->implode(', ');
    }

    public function descriptions(): Collection
    {
        return $this->optionValueAssignments->map(function (ProductVariantOptionValueAssignment $assignment) {
            return $assignment->productVariantOption->name . ': ' . $assignment->productVariantOptionValue->value;
        });
    }

    public function getBoxSizeAttribute(): int
    {
        return $this->product->box_size;
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
