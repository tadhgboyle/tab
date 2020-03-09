<?php

namespace App\Http\Controllers;

use App\Transactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserLimitsController extends Controller
{
    public static function findLimit($user, $category)
    {
        return DB::table('user_limits')->where([['user_id', $user], ['category', '=', $category]])->pluck('limit_per_day')->first();
    }

    public static function findSpent($user, $category)
    {
        $category_spent = 0.00;
        $tax_percent = SettingsController::getGst();
        $transactions = Transactions::where([['created_at', '>=', Carbon::now()->subDay()->toDateTimeString()], ['purchaser_id', '=', $user]])->get();
        foreach ($transactions as $transaction) {
            foreach (explode(", ", $transaction['products']) as $transaction_product) {
                if (strtolower($category) == DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('category')->first()) {
                    if (DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('category')->first() == "1") {
                        $tax_percent = ($tax_percent + SettingsController::getPst()) - 1;
                    }
                    $product_price = DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('price')->first();
                    $product_quantity = ltrim(strstr($transaction_product, '*'), '*');
                    $category_spent += ($product_price * $product_quantity) * $tax_percent;
                }
            }
        }
        return $category_spent;
    }
}
