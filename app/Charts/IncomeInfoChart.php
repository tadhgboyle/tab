<?php

namespace App\Charts;

use App\Helpers\Permission;
use App\Models\Transaction;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\RotationHelper;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Database\Query\Builder;

class IncomeInfoChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
        'permission:' . Permission::STATISTICS_INCOME_INFO,
    ];

    public function handler(Request $request): Chartisan
    {
        $normal_data = $this->createBuilder()
            ->where('returned', false)
            ->get();
        $returned_data = $this->createBuilder()
            ->where('returned', true)
            ->get();

        $normal_orders = $returned_orders = $labels = [];

        foreach ($normal_data as $normal_order) {
            $labels[] = Carbon::parse($normal_order['date'])->format('M jS Y');
            $normal_orders[] = $normal_order['total_price'];
            $found = false;

            foreach ($returned_data as $returned_order) {
                if ($normal_order['date'] === $returned_order['date']) {
                    $found = true;
                    $returned_orders[] = $returned_order['total_price'];
                    break;
                }
            }

            if (!$found) {
                $returned_orders[] = 0;
            }
        }

        return Chartisan::build()
            ->labels($labels)
            ->dataset('Returned', $returned_orders)
            ->dataset('Income', $normal_orders);
    }

    private function createBuilder(): Builder
    {
        $stats_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();

        return Transaction::query()
            ->when($stats_rotation_id !== '*', static function (Builder $builder) use ($stats_rotation_id) {
                $builder->where('rotation_id', $stats_rotation_id);
            })
            ->select(DB::raw('DATE(created_at) date, ROUND(SUM(total_price), 2) total_price'))
            ->orderBy('date')
            ->groupBy('date');
    }
}
