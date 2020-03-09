<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserLimitsController extends Controller
{
    public static function findSpent($user, $category)
    {   
        $category_spent = 0.00;
        $transactions = Transactions::where([['created_at', '>=', Carbon::now()->subDay()->toDateTimeString()], ['purchaser_id', '=', $user]])->get();
        foreach ($transactions as $transaction) {
            foreach (explode(", ", $transaction['products']) as $transaction_product) {
                if (strtolower($category) == DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('category')->first()) {
                    $product_price = DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('price')->first();
                    $product_quantity = ltrim(strstr($transaction_product, '*'), '*');
                    $category_spent += ($product_price * $product_quantity);
                }
            }
        }
        return $category_spent;
    }
}
