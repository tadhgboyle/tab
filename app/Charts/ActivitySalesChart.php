<?php

namespace App\Charts;

use App\Helpers\Permission;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityRegistration;
use Illuminate\Contracts\Database\Query\Builder;

class ActivitySalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
        'permission:' . Permission::STATISTICS_ACTIVITY_SALES,
    ];

    public function handler(Request $request): Chartisan
    {
        $stats_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();

        $sales = [];

        // TODO: no way to seed random rotation ID currently
        $activity_registrations = ActivityRegistration::query()
            ->when($stats_rotation_id !== '*', static function (Builder $builder) use ($stats_rotation_id) {
                $builder->where('rotation_id', $stats_rotation_id);
            })
            ->select(DB::raw('activity_id, count(*) as sold'))
            ->groupBy('activity_id')
            ->get();

        foreach ($activity_registrations as $registration) {
            $sales[] = [
                'name' => $registration->activity->name,
                /** @phpstan-ignore-next-line  */
                'sold' => $registration->sold
            ];
        }

        uasort($sales, static fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Attendees', array_column($sales, 'sold'));
    }
}
