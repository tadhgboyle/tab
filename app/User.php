<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rennokki\QueryCache\Traits\QueryCacheable;

class User extends Authenticatable
{
    use QueryCacheable;

    protected $cacheFor = 180;

    // Find how much a user has spent in total. 
    // Does not factor in returned items/orders.
    public static function findSpent($user)
    {
        return number_format(Transactions::where('purchaser_id', $user->id)->sum('total_price'), 2);
    }

    // Find how much a user has returned in total.
    // This will see if a whole order has been returned, or if not, check all items in an unreturned order.
    public static function findReturned($user)
    {
        $returned = 0.00;
        $transactions = Transactions::where('purchaser_id', $user->id)->get();

        foreach ($transactions as $transaction) {
            if ($transaction->status == 1) {
                $returned += $transaction->total_price;
                continue;
            }

            foreach (explode(", ", $transaction->products) as $transaction_product) {
                $product = OrderController::deserializeProduct($transaction_product);
                if ($product['returned'] > 0) {
                    $tax = $product['gst'];
                    if ($product['pst'] != "null") $tax += ($product['pst'] - 1);
                    $returned += $product['returned'] * $product['price'] * $tax;
                }
            }
        }

        return $returned;
    }

    // Find how much money a user owes. 
    // Taking their amount spent and subtracting the amount they have returned.
    public static function findOwing($user)
    {
        return number_format(User::findSpent($user) - User::findReturned($user), 2);
    }
}
