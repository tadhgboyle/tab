<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\ProductVariant;

trait InteractsWithStock
{
    public function hasStock(int $quantity): bool
    {
        if ($this->unlimited_stock || $this->stock_override) {
            return true;
        }

        if ($this->stock >= $quantity) {
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

    public function removeStock(int $remove_stock, ?string $reason, $causer): void
    {
        if ($this->unlimited_stock) {
            $this->createInventoryAdjustment(-$remove_stock, $reason, $causer);
            return;
        }

        if ($this->stock_override || ($this->stock >= $remove_stock)) {
            $this->decrement('stock', $remove_stock);
            $this->createInventoryAdjustment(-$remove_stock, $reason, $causer);
            return;
        }
    }

    public function adjustStock(int $new_stock, ?string $reason, $causer): void
    {
        $this->increment('stock', $new_stock);
        $this->createInventoryAdjustment($new_stock, $reason, $causer);
    }

    public function addBox(int $box_count, ?string $reason, $causer): void
    {
        $this->adjustStock($box_count * $this->box_size, $reason, $causer);
    }

    private function createInventoryAdjustment(int $adjustment, ?string $reason, $causer): void
    {
        $this->inventoryAdjustments()->create([
            'product_id' => $this instanceof ProductVariant ? $this->product_id : $this->id,
            'adjustment' => $adjustment,
            'new_quantity' => $this->stock,
            'reason' => $reason,
            'causer_id' => $causer->id,
            'causer_type' => $causer::class,
        ]);
    }
}
