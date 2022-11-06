<?php

namespace App\Models;

use App\Helpers\SettingsHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Product
 *
 * @property int $id
 * @property string $name
 * @property float $price
 * @property int $category_id
 * @property bool $pst
 * @property int $stock
 * @property bool $unlimited_stock
 * @property int $box_size
 * @property bool $stock_override
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Category $category
 * @method static \Database\Factories\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Query\Builder|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereBoxSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePst($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStockOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUnlimitedStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Product withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Product withoutTrashed()
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $casts = [
        'name' => 'string',
        'price' => 'float',
        'pst' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean', // stock is never checked
        'stock_override' => 'boolean', // stock can go negative
        'box_size' => 'integer',
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
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getPrice(): float
    {
        // TODO: tax calc and implementation
        $total_tax = 0.00;
        if ($this->pst) {
            $total_tax += resolve(SettingsHelper::class)->getPst();
        }

        $total_tax += resolve(SettingsHelper::class)->getGst();

        $total_tax--;

        return (float) number_format($this->price * $total_tax, 2);
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
