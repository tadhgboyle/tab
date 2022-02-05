<?php

namespace App\Helpers;

use App\Models\Settings;

class SettingsHelper extends Helper
{
    private float $gst;
    private float $pst;

    public function getGst(): float
    {
        return $this->gst ??= Settings::firstWhere('setting', 'gst')->value;
    }

    public function getPst(): float
    {
        return $this->pst ??= Settings::firstWhere('setting', 'pst')->value;
    }
}
