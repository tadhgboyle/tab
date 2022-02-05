<?php

namespace App\Charts;

use App\Models\Product;
use Chartisan\PHP\Chartisan;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
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
        $stats_rotation_id = resolve(RotationHelper::class)->getCurrentRotation()->id;

        foreach ($products as $product) {
            $sold = $product->findSold($stats_rotation_id);
            if ($sold < 1) {
                continue;
            }

            $sales[] = ['name' => $product->name, 'sold' => $sold];
        }

        uasort($sales, static fn ($a, $b) => $a['sold'] > $b['sold'] ? -1 : 1);
        $sales = array_slice($sales, 0, 50);

        return Chartisan::build()
            ->labels(array_column($sales, 'name'))
            ->dataset('Sold', array_column($sales, 'sold'));
    }
}
