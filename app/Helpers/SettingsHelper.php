<?php

namespace App\Helpers;

use App\Models\Settings;

class SettingsHelper
{
    private float $gst;
    private float $pst;

    private string $orderPrefix;

    private string $orderSuffix;

    // TODO use firstOrCreate so tests dont need to set them?
    public function getGst(): float
    {
        return $this->gst ??= Settings::firstWhere('setting', 'gst')->value;
    }

    public function getPst(): float
    {
        return $this->pst ??= Settings::firstWhere('setting', 'pst')->value;
    }

    public function getOrderPrefix(): string
    {
        return $this->orderPrefix ??= Settings::firstOrCreate(
            ['setting' => 'order_prefix'],
            ['value' => '#']
        )->value;
    }

    public function getOrderSuffix(): string
    {
        return $this->orderSuffix ??= Settings::firstOrCreate(
            ['setting' => 'order_suffix'],
            ['value' => '']
        )->value;
    }
}
