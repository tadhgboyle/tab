<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOptionValueAssignment extends Model
{
    protected $fillable = ['product_variant_option_id', 'product_variant_option_value_id'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productVariantOption(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class)->withTrashed();
    }

    public function productVariantOptionValue(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOptionValue::class)->withTrashed();
    }
}
