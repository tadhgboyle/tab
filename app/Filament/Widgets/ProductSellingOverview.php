<?php

namespace App\Filament\Widgets;

use App\Models\TransactionProduct;
use Filament\Widgets\ChartWidget;

class ProductSellingOverview extends ChartWidget
{
    protected static ?string $heading = 'Top selling products';

    protected function getData(): array
    {
        $products = TransactionProduct::query()
            ->join('products', 'transaction_products.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(transaction_products.quantity) as total_quantity'))
            ->groupBy('products.id', 'products.name')
            ->limit(20)
            ->get()
            ->sortByDesc('total_quantity');

        return [
            'datasets' => [
                [
                    'label' => 'Returned',
                    'data' => $products->map->total_quantity->values(),
                ],
            ],
            'labels' => $products->map->name->values(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
