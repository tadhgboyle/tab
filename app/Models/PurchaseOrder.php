<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'delivery_date' => 'date',
        'status' => PurchaseOrderStatus::class,
    ];

    public function products()
    {
        return $this->hasMany(PurchaseOrderProduct::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function totalQuantity(): int
    {
        return $this->products->reduce(function (int $carry, PurchaseOrderProduct $product) {
            return $carry + $product->quantity;
        }, 0);
    }

    public function totalCost(): Money
    {
        return $this->products->reduce(function (Money $carry, PurchaseOrderProduct $product) {
            return $carry->add($product->cost->multiply($product->quantity));
        }, Money::parse(0));
    }

    public function receivedQuantity(): int
    {
        return $this->products->sum('received_quantity');
    }

    public function receivedCost(): Money
    {
        return $this->products->reduce(function (Money $carry, PurchaseOrderProduct $product) {
            return $carry->add($product->cost->multiply($product->received_quantity));
        }, Money::parse(0));
    }

    public function outstandingQuantity(): int
    {
        return $this->products->sum('quantity') - $this->products->sum('received_quantity');
    }

    public function outstandingCost(): Money
    {
        return $this->products->reduce(function (Money $carry, PurchaseOrderProduct $product) {
            return $carry->add($product->cost->multiply($product->quantity - $product->received_quantity));
        }, Money::parse(0));
    }
}
