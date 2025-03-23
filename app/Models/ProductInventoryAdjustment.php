<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductInventoryAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'adjustment',
        'new_quantity',
        'reason',
        'causer_id',
        'causer_type',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function causer(): MorphTo
    {
        return $this->morphTo('causer');
    }
}
