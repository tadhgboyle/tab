<?php

namespace App\Charts;

use App\Helpers\RotationHelper;
use App\Models\Product;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use ConsoleTVs\Charts\BaseChart;

class ItemSalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
    ];

    public function handler(Request $request): Chartisan
    {
        $sales = [];

        $products = Product::all();
        $stats_rotation_id = RotationHelper::getInstance()->getStatisticsRotation();

        foreach ($products as $product) {
            $sold = $product->findSold($stats_rotation_id);
            if ($sold < 1) {
                continue;
            }

            $sales[] = ['name' => $product->name, 'sold' => $sold];
        }

        uasort($sales, fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);
        $sales = array_slice($sales, 0, 50);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Sold', array_column($sales, 'sold'));
    }
}
