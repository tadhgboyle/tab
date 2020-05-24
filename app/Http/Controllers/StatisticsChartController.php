<?php

namespace App\Http\Controllers;

use App\Charts\StatisticsChart;
use App\Products;
use App\Transactions;

class StatisticsChartController extends Controller
{
    public static function recentOrders()
    {
        $recentorders = new StatisticsChart;

        $normal_data = Transactions::selectRaw('COUNT(*) as count, MONTH(created_at) month')->groupBy('month')->where('status', 0)->get();
        $returned_data = Transactions::selectRaw('COUNT(*) as count, MONTH(created_at) month')->groupBy('month')->where('status', 1)->get();

        $labels = array();

        $normal_orders = array();
        foreach ($normal_data as $child) {
            array_push($labels, date('F', mktime(0, 0, 0, $child['month'], 10)));
            array_push($normal_orders, $child['count']);
        }
        $returned_orders = array();
        foreach ($returned_data as $child) array_push($returned_orders, $child['count']);

        $recentorders->labels($labels);
        $recentorders->dataset('Normal Orders', 'line', $normal_orders)->color("rgb(137, 46, 234)")->fill(true);
        $recentorders->dataset('Returned Orders', 'line', array_pad($returned_orders, -(count($normal_orders)), 0))->color("rgb(62, 113, 223)")->fill(false);
        return $recentorders;
    }

    public static function popularItems()
    {
        $popularitems = new StatisticsChart;

        $labels = array();
        $sales = array();
        foreach (Products::all() as $product) {
            $sold = Products::findSold($product->id);
            if ($sold == 0) continue;
            array_push($labels, $product->name);
            array_push($sales, $sold);
        }

        $popularitems->labels($labels);
        $popularitems->dataset('Items', 'bar', $sales)->color("rgb(255, 99, 132)");
        return $popularitems;
    }
}
