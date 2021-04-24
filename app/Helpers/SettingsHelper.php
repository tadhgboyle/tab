<?php

namespace App\Helpers;

use App\Models\Settings;

class SettingsHelper
{
    private static SettingsHelper $_instance;

    private float $_gst;
    private float $_pst;
    private int $_stats_time;

    public static function getInstance(): SettingsHelper
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new SettingsHelper();
        }

        return self::$_instance;
    }

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

    public function getStatsTime(): float
    {
        if (!isset($this->_stats_time)) {
            $this->_stats_time = Settings::where('setting', 'stats_time')->pluck('value')->first();
        }

        return $this->_stats_time;
    }
}
