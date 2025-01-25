<?php

namespace App\Traits;

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
}
