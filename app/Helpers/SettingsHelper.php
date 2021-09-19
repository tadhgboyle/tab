<?php

namespace App\Helpers;

use App\Models\Settings;

class SettingsHelper extends Helper
{
    private float $gst;
    private float $pst;

    public function getGst(): float
    {
        return $this->gst ??= Settings::where('setting', 'gst')->pluck('value')->first();
    }

    public function getPst(): float
    {
        return $this->pst ??= Settings::where('setting', 'pst')->pluck('value')->first();
    }
}
