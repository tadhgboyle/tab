<?php

namespace App\Filament\Widgets;

use App\Models\TransactionProduct;
use Filament\Widgets\ChartWidget;

class ProductReturnedOverview extends ChartWidget
{
    protected static ?string $heading = 'Top returned products';

    protected function getData(): array
    {
        $products = TransactionProduct::query()
            ->join('products', 'transaction_products.product_id', '=', 'products.id')
            ->select('products.name', \DB::raw('SUM(transaction_products.returned) as total_returned'))
            ->groupBy('products.id', 'products.name')
            ->limit(20)
            ->get()
            ->sortByDesc('total_returned')
            ->filter(fn ($product) => $product->total_returned > 0);

        return [
            'datasets' => [
                [
                    'label' => 'Returned',
                    'data' => $products->map->total_returned->values(),
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
