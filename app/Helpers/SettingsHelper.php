<?php

namespace App\Helpers;

use App\Settings;

class SettingsHelper
{

    private static ?SettingsHelper $_instance = null;

    private ?float $_gst = null;
    private ?float $_pst = null;
    private ?int $_stats_time = null;

    public static function getInstance(): SettingsHelper
    {
        if (self::$_instance == null) {
            self::$_instance = new SettingsHelper();
        }

        return self::$_instance;
    }

    public function getGst(): float
    {
        if ($this->_gst == null) {
            $this->_gst = Settings::where('setting', 'gst')->pluck('value')->first();
        }

        return $this->_gst;
    }

    public function getPst(): float
    {
        if ($this->_pst == null) {
            $this->_pst = Settings::where('setting', 'pst')->pluck('value')->first();
        }

        return $this->_pst;
    }

    public function getStatsTime(): float
    {
        if ($this->_stats_time == null) {
            $this->_stats_time = Settings::where('setting', 'stats_time')->pluck('value')->first();
        }

        return $this->_stats_time;
    }
}
