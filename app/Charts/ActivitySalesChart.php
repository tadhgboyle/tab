<?php

namespace App\Charts;

use App\Helpers\Permission;
use DB;
use App\Models\Activity;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\BaseChart;

class ActivitySalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
        'permission:' . Permission::STATISTICS_ACTIVITY_SALES,
    ];

    public function handler(Request $request): Chartisan
    {
        // $stats_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();
        // TODO: Store rotation_id when creating activity_transaction

        $sales = [];

        $activity_transactions = DB::table('activity_transactions')
            ->select(DB::raw('activity_id, count(*) as sold'))
            ->groupBy('activity_id')
            ->get();

        foreach ($activity_transactions as $activity_transaction) {
            $activity = Activity::find($activity_transaction->activity_id);
            $sales[] = [
                'name' => $activity->name,
                'sold' => $activity_transaction->sold
            ];
        }

        uasort($sales, static fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Attendees', array_column($sales, 'sold'));
    }
}
