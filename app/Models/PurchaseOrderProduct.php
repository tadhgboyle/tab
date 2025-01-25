<?php

namespace App\Models;

use App\Enums\PurchaseOrderStatus;
use Cknow\Money\Casts\MoneyIntegerCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'cost' => MoneyIntegerCast::class,
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function receive()
    {
        if ($this->productVariant?->exists) {
            $this->productVariant->adjustStock($this->quantity);
        } else {
            $this->product->adjustStock($this->quantity);
        }

        $this->update([
            'received_quantity' => $this->quantity,
        ]);

        if ($this->purchaseOrder->products()->whereColumn('received_quantity', '<', 'quantity')->doesntExist()) {
            $this->purchaseOrder->update([
                'status' => PurchaseOrderStatus::Completed,
            ]);
        }
    }
}
