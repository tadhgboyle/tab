<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantOptionValue extends Model
{
    use SoftDeletes;

    protected $fillable = ['value'];

    public function productVariantOption(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class);
    }
}
