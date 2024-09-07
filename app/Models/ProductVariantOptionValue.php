<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ProductVariantOptionValue extends Model
{
    use SoftDeletes;

    protected $fillable = ['value'];

    public function variants(): HasManyThrough
    {
        return $this->hasManyThrough(ProductVariant::class, ProductVariantOptionValueAssignment::class, null, 'id', null, 'product_variant_id');
    }

    public function productVariantOption(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class);
    }
}
