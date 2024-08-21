<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOptionValue extends Model
{
    protected $fillable = ['value'];

    public function productVariantOption(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class);
    }
}
