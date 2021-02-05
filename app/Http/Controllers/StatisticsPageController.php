<?php

namespace App\Http\Controllers;

class StatisticsPageController extends Controller
{

    public function view()
    {
        return view('pages.statistics.statistics', ['stats_time' => SettingsController::getInstance()->getStatsTime()]);
    }
    
}
