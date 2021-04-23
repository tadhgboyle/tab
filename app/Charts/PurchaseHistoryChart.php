<?php

namespace App\Charts;

use App\Transaction;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Helpers\SettingsHelper;
use ConsoleTVs\Charts\BaseChart;

class PurchaseHistoryChart extends BaseChart
{
    public ?array $middlewares = ['auth']; // TODO: use HasPermission::class middleware, just dont know how to pass the permission

    public function handler(Request $request): Chartisan
    {
        $normal_data = Transaction::where([['created_at', '>=', Carbon::now()->subDays(SettingsHelper::getInstance()->getStatsTime())->toDateTimeString()], ['status', 0]])->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();
        $returned_data = Transaction::where([['created_at', '>=', Carbon::now()->subDays(SettingsHelper::getInstance()->getStatsTime())->toDateTimeString()], ['status', 1]])->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();

        $normal_orders = $returned_orders = $labels = [];

        foreach ($normal_data as $normal_order) {
            array_push($labels, Carbon::parse($normal_order['date'])->format('M jS Y'));
            array_push($normal_orders, $normal_order['count']);
            $found = false;

            foreach ($returned_data as $returned_order) {
                if ($normal_order['date'] == $returned_order['date']) {
                    $found = true;
                    array_push($returned_orders, $returned_order['count']);
                    break;
                }
            }

            if (!$found) {
                array_push($returned_orders, 0);
            }
        }

        return Chartisan::build()
            ->labels($labels)
            ->dataset('Returned Orders', $returned_orders)
            ->dataset('Orders', $normal_orders);
    }
}
