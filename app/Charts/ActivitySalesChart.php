<?php

namespace App\Charts;

use App\Models\Activity;
use Chartisan\PHP\Chartisan;
use DB;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use ConsoleTVs\Charts\BaseChart;

class ActivitySalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
    ];

    public function handler(Request $request): Chartisan
    {
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
