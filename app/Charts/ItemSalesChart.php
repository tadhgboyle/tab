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
        foreach ($products as $product) {
            $sold = $product->findSold(SettingsController::getInstance()->getStatsTime());
            if ($sold < 1) {
                continue;
            }

            array_push($sales, ['name' => $product->name, 'sold' => $sold]);
        }

        uasort($sales, function ($a, $b) {
            return ($a['sold'] > $b['sold'] ? -1 : 1);
        });

        // $popular_items = new StatisticsChart;
        // $popular_items->labels(array_column($sales, 'name'));
        // $popular_items->dataset('Sold', 'bar', array_column($sales, 'sold'))->color("rgb(72, 187, 120)");
        // return $popular_items;

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Sold', array_column($sales, 'sold'));
    }
}
