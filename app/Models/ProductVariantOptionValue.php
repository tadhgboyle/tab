<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariantOptionValue extends Model
{
    use SoftDeletes;

    protected $fillable = ['value'];

    public function productVariantOption(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class);
    }
}
