<?php

namespace App\Charts;

use App\Models\Product;
use App\Models\User;
use Chartisan\PHP\Chartisan;
use Cookie;
use Illuminate\Http\Request;
use App\Helpers\RotationHelper;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Support\Collection;

class ProductSalesChart extends BaseChart
{
    public ?array $middlewares = [
        'auth',
        'permission:statistics_product_sales',
    ];

    public function handler(Request $request): Chartisan
    {
        $sales = collect();

        /** @var Collection<Product> $products */
        $products = Product::all();

        $stats_rotation_id = resolve(RotationHelper::class)->getStatisticsRotationId();

        foreach ($products as $product) {
            $sold = $product->findSold($stats_rotation_id);
            if ($sold >= 1) {
                $sales->add([
                    'name' => $product->name,
                    'sold' => $sold
                ]);
            }
        }

        $sales = $sales->sort(static function ($a, $b) {
            return $a['sold'] > $b['sold']
                ? -1
                : 1;
        })->take(50);

        return Chartisan::build()
            ->labels($sales->pluck('name')->toArray())
            ->dataset('Sold', $sales->pluck('sold')->toArray());
    }
}
