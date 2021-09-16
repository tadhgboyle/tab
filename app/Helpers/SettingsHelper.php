<?php

namespace App\Helpers;

use App\Models\Settings;

class SettingsHelper extends Helper
{
    private float $_gst;
    private float $_pst;
    private int $_stats_rotation_id;

    public function getGst(): float
    {
        return $this->_gst ??= Settings::where('setting', 'gst')->pluck('value')->first();
    }

    public function getPst(): float
    {
        return $this->_pst ??= Settings::where('setting', 'pst')->pluck('value')->first();
    }
}
