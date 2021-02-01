<?php

namespace App\Http\Controllers;

use App\Transactions;
use App\Products;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class UserLimitsController extends Controller
{

    public static function getInfo($user_id, $category)
    {
        $info = DB::table('user_limits')->where([['user_id', $user_id], ['category', $category]])->get(['duration', 'limit_per']);
        $object = new stdClass;
        if (isset($info->duration)) {
            $object->duration = $info->duration;
        } else {
            $object->duration = 'week';
        }
        if (isset($info->limit_per)) {
            $object->limit_per = $info->limit_per;
        } else {
            // Usually this will happen when we make a new category after a user was made
            $object->limit_per = -1;
        }
        return $object;
    }

    public static function findSpent(int $user_id, string $category, object $info)
    {
        // First, if they have unlimited money for this category, let's grab all their transactions
        if ($info->limit_per == -1) {
            $transactions = Transactions::where([['purchaser_id', $user_id], ['status', 0]])->get();
        }
        // Determine how far back to grab transactions from
        else {
            $transactions = Transactions::where([['created_at', '>=', Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString()], ['purchaser_id', $user_id], ['status', 0]])->get();
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
