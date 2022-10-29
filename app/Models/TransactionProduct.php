<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransactionProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'category_id',
        'quantity',
        'price',
        'gst',
        'pst',
        'returned',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'float',
        'gst' => 'float',
        'pst' => 'float',
        'returned' => 'integer',
    ];

    protected $with = [
        'product',
    ];

    public static function from(
        Product $product,
        int $quantity,
        float $gst,
        ?float $pst = null,
        int $returned = 0
    ): TransactionProduct {
        return new TransactionProduct([
            'product_id' => $product->id,
            'category_id' => $product->category_id,
            'quantity' => $quantity,
            'price' => $product->price,
            'gst' => $gst,
            'pst' => $pst,
            'returned' => $returned,
        ]);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }

    public function category(): HasOne
    {
        return $this->hasOne(Category::class);
    }

    public function getTax(): float
    {
        if ($this->pst === null) {
            return $this->gst;
        }

        return ($this->pst + $this->gst) - 1;
    }
}
