<?php

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Models\Activity;
use ConsoleTVs\Charts\BaseChart;

class ActivitySalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
    ];

    public function handler(Request $request): Chartisan
    {
        $sales = [];

        $activities = Activity::all();
        $stats_time = SettingsHelper::getInstance()->getStatsTime();
        foreach ($activities as $activity) {
            $sold = $activity->getCurrentAttendees()->count();
            if ($sold < 1) {
                continue;
            }

            $sales[] = ['name' => $activity->name, 'sold' => $sold];
        }

        uasort($sales, fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Attendees', array_column($sales, 'sold'));
    }
}
