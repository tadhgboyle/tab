<?php

namespace App\Models;

use Cknow\Money\Money;
use App\Helpers\TaxHelper;
use App\Enums\ProductStatus;
use Illuminate\Support\Carbon;
use App\Traits\InteractsWithStock;
use Illuminate\Support\Collection;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    use InteractsWithStock;

    protected $casts = [
        'status' => ProductStatus::class,
        'price' => MoneyIntegerCast::class,
        'cost' => MoneyIntegerCast::class,
        'pst' => 'boolean',
        'stock' => 'integer',
        'unlimited_stock' => 'boolean',
        'stock_override' => 'boolean',
        'box_size' => 'integer',
        'restore_stock_on_return' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'sku',
        'status',
        'price',
        'cost',
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

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function variantOptions(): HasMany
    {
        return $this->hasMany(ProductVariantOption::class);
    }

    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, OrderProduct::class, null, 'id', 'id', 'order_id');
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function hasVariants(): bool
    {
        return $this->variants->isNotEmpty();
    }

    public function hasAllVariantCombinations(): bool
    {
        return $this->variants->count() === $this->variantOptions->reduce(function ($carry, ProductVariantOption $option) {
            return $carry * $option->values->count();
        }, 1);
    }

    // TODO: could this be getPriceAttribute()?
    public function getVariantPriceRange(): string
    {
        if ($this->hasVariants()) {
            $min = $this->variants->min('price');
            $max = $this->variants->max('price');

            if ($min === $max) {
                return $min;
            }

            return "{$min} - {$max}";
        }

        return $this->price;
    }

    public function getVariantCostRange(): ?string
    {
        if ($this->hasVariants()) {
            $min = $this->variants->min('cost');
            $max = $this->variants->max('cost');

            if ($min === $max) {
                return $min;
            }

            return "{$min} - {$max}";
        }

        return $this->cost;
    }

    public function isActive(): bool
    {
        return $this->status === ProductStatus::Active;
    }

    public function getPriceAfterTax(): Money
    {
        return TaxHelper::calculateFor($this->price, 1, $this->pst);
    }

    public function getStockAttribute(int $baseStock): int
    {
        if ($this->hasVariants()) {
            return $this->variants->sum('stock');
        }

        return $baseStock;
    }

    public function recentOrders(): Collection
    {
        return $this->orders()->latest()->limit(10)->get();
    }

    public function totalRecentOrders(): int
    {
        return $this->orders()->where('orders.created_at', '>=', Carbon::now()->subDays(30))->count();
    }

    public function totalRecentUnits(): int
    {
        return $this->orderProducts()->where('created_at', '>=', Carbon::now()->subDays(30))->sum('quantity');
    }

    public function totalRecentRevenue(): Money
    {
        return Money::parse($this->orderProducts()->where('created_at', '>=', Carbon::now()->subDays(30))->sum('total_price'));
    }

    public function profitMargin(): ?float
    {
        if ($this->cost->getAmount() === 0) {
            return 100;
        }

        if ($this->hasVariants()) {
            return null;
        }

        return round(($this->price->getAmount() - $this->cost->getAmount()) / $this->price->getAmount() * 100, 2);
    }
}
