<?php

namespace App\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $casts = [
        'price' => MoneyIntegerCast::class,
    ];

    protected $fillable = ['sku', 'price'];

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
        return $this->optionValueAssignments->map(function (ProductVariantOptionValueAssignment $assignment) {
            return $assignment->productVariantOption->name . ': ' . $assignment->productVariantOptionValue->value;
        })->implode(', ');
    }
}
