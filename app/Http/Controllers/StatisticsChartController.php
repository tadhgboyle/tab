<?php

namespace App\Http\Controllers;

use App\Charts\StatisticsChart;
use App\Products;
use App\Transactions;
use Illuminate\Support\Carbon;

class StatisticsChartController extends Controller
{
    // $lookBack = 90 => Last 3 months
    // $lookBack = 30 => Last 1 month
    // $lookBack = 14 => Last 2 weeks
    // $lookBack = 7 => Last 1 week
    // $lookBack = 1 => Last 1 day
    public static function recentOrders($lookBack)
    {
        $recentorders = new StatisticsChart;

        // TODO: Make this one select statement
        $normal_data = Transactions::where('created_at', '>=', Carbon::now()->subDays($lookBack)->toDateTimeString())->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();
        $returned_data = Transactions::where([['created_at', '>=', Carbon::now()->subDays($lookBack)->toDateTimeString()], ['status', '1']])->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();

        $labels = array();
        $normal_orders = array();

        foreach ($normal_data as $child) {
            array_push($labels, $child['date']);
            array_push($normal_orders, $child['count']);
        }
        $returned_orders = array();
        foreach ($returned_data as $child) array_push($returned_orders, $child['count']);

        $recentorders->labels($labels);
        $recentorders->dataset('Normal Orders', 'line', $normal_orders)->color("rgb(137, 46, 234)")->fill(true);
        $recentorders->dataset('Returned Orders', 'line', array_pad($returned_orders, - (count($normal_orders)), 0))->color("rgb(62, 113, 223)")->fill(false);
        return $recentorders;
    }

    public static function popularItems($lookBack)
    {
        $popularitems = new StatisticsChart;

        $sales = array();
        
        foreach (Products::all() as $product) {
            $sold = Products::findSold($product->id, $lookBack);
            if ($sold == 0) continue;
            array_push($sales, ['name' => $product->name, 'sold' => $sold]);
        }

        uasort($sales, function ($a, $b) {
            return ($a['sold'] > $b['sold'] ? -1 : 1);
        });

        $popularitems->labels(array_column($sales, 'name'));
        $popularitems->dataset('Sold', 'bar', array_column($sales, 'sold'))->color("rgb(255, 99, 132)");
        return $popularitems;
    }

    // TODO: This
    public static function popularCategories($lookBack)
    {
    }
}
