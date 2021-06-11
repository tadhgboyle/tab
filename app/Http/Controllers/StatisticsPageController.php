<?php

namespace App\Http\Controllers;

use App\Helpers\SettingsHelper;

class StatisticsPageController extends Controller
{
    public function view()
    {
        return view(
            'pages.statistics.statistics',
            [
                'stats_time' => SettingsHelper::getInstance()->getStatsTime()
            ]
        );
    }
}
