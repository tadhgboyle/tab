<?php

namespace App\Charts;

use App\Helpers\Permission;
use App\Models\Transaction;
use Chartisan\PHP\Chartisan;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\RotationHelper;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Support\Facades\DB;

class OrderHistoryChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
        'permission:' . Permission::STATISTICS_ORDER_HISTORY,
    ];

    // TODO: How should we display semi-Returned orders?
    public function handler(Request $request): Chartisan
    {
        $normal_data = $this->createBuilder()->where('returned', false)->get();
        $returned_data = $this->createBuilder()->where('returned', true)->get();

        $normal_orders = $returned_orders = $labels = [];

        foreach ($normal_data as $normal_order) {
            $labels[] = Carbon::parse($normal_order['date'])->format('M jS Y');
            $normal_orders[] = $normal_order['count'];
            $found = false;

            foreach ($returned_data as $returned_order) {
                if ($normal_order['date'] === $returned_order['date']) {
                    $found = true;
                    $returned_orders[] = $returned_order['count'];
                    break;
                }
            }

            if (!$found) {
                $returned_orders[] = 0;
            }
        }

        return Chartisan::build()
            ->labels($labels)
            ->dataset('Returned Orders', $returned_orders)
            ->dataset('Orders', $normal_orders);
    }

    private function createBuilder(): Builder
    {
        $stats_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();

        return Transaction::query()
            ->when($stats_rotation_id !== '*', static function (Builder $builder) use ($stats_rotation_id) {
                $builder->where('rotation_id', $stats_rotation_id);
            })
            ->select(DB::raw('COUNT(*) AS count, DATE(created_at) date'))
            ->orderBy('date')
            ->groupBy('date');
    }
}
