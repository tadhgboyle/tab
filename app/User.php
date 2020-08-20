<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rennokki\QueryCache\Traits\QueryCacheable;

class User extends Authenticatable
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['balance'];

    // Find how much a user has spent in total. 
    // Does not factor in returned items/orders.
    public static function findSpent(User $user): float
    {
        return number_format(Transactions::where('purchaser_id', $user->id)->sum('total_price'), 2);
    }

    // Find how much a user has returned in total.
    // This will see if a whole order has been returned, or if not, check all items in an unreturned order.
    public static function findReturned(User $user): float
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

        return number_format($returned, 2);
    }

    // Find how much money a user owes. 
    // Taking their amount spent and subtracting the amount they have returned.
    public static function findOwing(User $user): float
    {
        return number_format(User::findSpent($user) - User::findReturned($user), 2);
    }
}
