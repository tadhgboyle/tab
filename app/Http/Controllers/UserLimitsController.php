<?php

namespace App\Http\Controllers;

use App\Transactions;
use App\Products;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserLimitsController extends Controller
{
    public static function findDuration($user, $category)
    {
        return DB::table('user_limits')->where([['user_id', $user], ['category', $category]])->pluck('duration')->first() == 0 ? "day" : "week";
    }

    public static function findLimit($user, $category)
    {
        $limit = DB::table('user_limits')->where([['user_id', $user], ['category', $category]])->pluck('limit_per')->first();
        // Usually this will happen when we make a new category after a user was made
        return $limit ?? "-1";
    }

    public static function findSpent($user, $category, $duration)
    {
        // First, if they have unlimited money for this category, let's grab all their transactions
        if (self::findLimit($user, $category) == -1) {
            $transactions = Transactions::where([['purchaser_id', $user], ['status', 0]])->get();
        }
        // Determine how far back to grab transactions from
        else {
            $transactions = Transactions::where([['created_at', '>=', Carbon::now()->subDays($duration == 'day' ? 1 : 7)->toDateTimeString()], ['purchaser_id', $user], ['status', 0]])->get();
        }

        $category_spent = 0.00;

        // Loop applicable transactions, then do a bunch of wacky shit
        foreach ($transactions as $transaction) {
            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            foreach (explode(", ", $transaction['products']) as $transaction_product) {
                if (strtolower($category) == Products::find(strtok($transaction_product, "*"))->category) {
                    $item_info = OrderController::deserializeProduct($transaction_product);
                    $tax_percent = $item_info['gst'];
                    if ($item_info['pst'] != "null") $tax_percent += $item_info['pst'] - 1;
                    $quantity_available = $item_info['quantity'] - $item_info['returned'];
                    $category_spent += ($item_info['price'] * $quantity_available) * $tax_percent;
                }
            }
        }

        return $category_spent;
    }
    
}
