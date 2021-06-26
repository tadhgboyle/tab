<?php

namespace App\Charts;

use App\Helpers\RotationHelper;
use App\Models\Activity;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
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
        $stats_rotation_id = RotationHelper::getInstance()->getStatisticsRotation();
        // TODO: Use activity transactions table instead
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
