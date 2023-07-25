<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    // Used to check if items in order have enough stock BEFORE using removeStock() to remove it.
    // If we didn't use this, then stock would be adjusted and then the order could fail, resulting in inaccurate stock.
    public function hasStock(int $quantity): bool
    {
        if ($this->unlimited_stock) {
            return true;
        }

        if ($this->stock >= $quantity || $this->stock_override) {
            return true;
        }

        return false;
    }

    public function getStock(): int|string
    {
        if ($this->unlimited_stock) {
            return '<i>Unlimited</i>';
        }

        return $this->stock;
    }

    public function removeStock(int $remove_stock): bool
    {
        if ($this->unlimited_stock) {
            return true;
        }

        if ($this->stock_override || ($this->getStock() >= $remove_stock)) {
            $this->decrement('stock', $remove_stock);
            return true;
        }

        return false;
    }

    public function adjustStock(int $new_stock): false|int
    {
        return $this->increment('stock', $new_stock);
    }

    public function addBox(int $box_count): bool|int
    {
        return $this->adjustStock($box_count * $this->box_size);
    }

    public function findSold(string|int $rotation_id): int
    {
        return TransactionProduct::when($rotation_id !== '*', static function (Builder $query) use ($rotation_id) {
            $query->whereHas('transaction', function (Builder $query) use ($rotation_id) {
                $query->where('rotation_id', $rotation_id);
            });
        })->where('product_id', $this->id)->chunkMap(static function (TransactionProduct $transactionProduct) {
            return $transactionProduct->quantity - $transactionProduct->returned;
        })->sum();
    }
}
