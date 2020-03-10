<?php

namespace App\Http\Controllers;

use App\Transactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserLimitsController extends Controller
{
    public static function findDuration($user, $category)
    {
        return DB::table('user_limits')->where([['user_id', $user], ['category', '=', $category]])->pluck('duration')->first() == 0 ? "day" : "week";
    }

    public static function findLimit($user, $category)
    {
        return DB::table('user_limits')->where([['user_id', $user], ['category', '=', $category]])->pluck('limit_per')->first();
    }

    public static function findSpent($user, $category)
    {
        $category_spent = 0.00;
        $tax_percent = SettingsController::getGst();
        $transactions = Transactions::where([['created_at', '>=', Carbon::now()->subDay()->toDateTimeString()], ['purchaser_id', $user], ['status', '0']])->get();
        foreach ($transactions as $transaction) {
            foreach (explode(", ", $transaction['products']) as $transaction_product) {
                if (strtolower($category) == DB::table('products')->where('id', '=', strtok($transaction_product, "*"))->pluck('category')->first()) {
                    $item_info = OrderController::deserializeProduct($transaction_product);
                    if (DB::table('products')->where('id', '=', $item_info['id'])->pluck('pst')->first() == "1") {
                        $tax_percent = ($tax_percent + SettingsController::getPst()) - 1;
                    }
                    $category_spent += ($item_info['price'] * $item_info['quantity']) * $tax_percent;
                }
            }
        }
        return $category_spent;
    }
}
