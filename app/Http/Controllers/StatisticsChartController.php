<?php

namespace App\Http\Controllers;

use App\Charts\StatisticsChart;
use App\Product;
use App\Transaction;
use Illuminate\Support\Carbon;

class StatisticsChartController extends Controller
{
    public static function orderInfo($days_ago): StatisticsChart
    {
        $normal_data = Transaction::where([['created_at', '>=', Carbon::now()->subDays($days_ago)->toDateTimeString()], ['status', 0]])->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();
        $returned_data = Transaction::where([['created_at', '>=', Carbon::now()->subDays($days_ago)->toDateTimeString()], ['status', 1]])->selectRaw('COUNT(*) AS count, DATE(created_at) date')->groupBy('date')->get();

        $normal_orders = array();
        $returned_orders = array();
        $labels = array();

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

        $recent_orders = new StatisticsChart;
        $recent_orders->labels($labels);
        $recent_orders->dataset('Returned Orders', 'line', $returned_orders)->lineTension(0)->color("rgb(245, 101, 101)");
        $recent_orders->dataset('Orders', 'line', $normal_orders)->lineTension(0)->color("rgb(72, 187, 120)");
        return $recent_orders;
    }

    public static function itemInfo($days_ago): StatisticsChart
    {
        $sales = array();

        $products = Product::where('deleted', false)->get();
        foreach ($products as $product) {
            $sold = Product::findSold($product->id, $days_ago);
            if ($sold < 1) {
                continue;
            }

            array_push($sales, ['name' => $product->name, 'sold' => $sold]);
        }

        uasort($sales, function ($a, $b) {
            return ($a['sold'] > $b['sold'] ? -1 : 1);
        });

        $popular_items = new StatisticsChart;
        $popular_items->labels(array_column($sales, 'name'));
        $popular_items->dataset('Sold', 'bar', array_column($sales, 'sold'))->color("rgb(72, 187, 120)");
        return $popular_items;
    }
    
}
