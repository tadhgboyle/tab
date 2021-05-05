<?php

namespace App\Charts;

use App\Models\Product;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use ConsoleTVs\Charts\BaseChart;

class ItemSalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
    ];

    public function handler(Request $request): Chartisan
    {
        $sales = [];

        $products = Product::where('deleted', false)->get();
        $stats_time = SettingsHelper::getInstance()->getStatsTime();
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
