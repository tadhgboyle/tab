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
        if (!isset($this->_gst)) {
            $this->_gst = Settings::where('setting', 'gst')->pluck('value')->first();
        }

        return $this->_gst;
    }

    public function getPst(): float
    {
        if (!isset($this->_pst)) {
            $this->_pst = Settings::where('setting', 'pst')->pluck('value')->first();
        }

        return $this->_pst;
    }
}
