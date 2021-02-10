<?php

namespace App\Charts;

use App\Product;
use App\Http\Controllers\SettingsController;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;
use Chartisan\PHP\Chartisan;

class ItemSalesChart extends BaseChart
{

    public function handler(Request $request): Chartisan
    {
        $sales = array();

        $products = Product::where('deleted', false)->get();
        $stats_time = SettingsController::getInstance()->getStatsTime();
        foreach ($products as $product) {
            $sold = $product->findSold($stats_time);
            if ($sold < 1) {
                continue;
            }

            array_push($sales, ['name' => $product->name, 'sold' => $sold]);
        }

        uasort($sales, fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Sold', array_column($sales, 'sold'));
    }
}
