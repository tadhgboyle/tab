<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['balance'];

    // protected $casts = [
    //     'role' => Roles::class
    // ];
    
    // TODO: getViewUrl and getEditUrl

    // Find how much a user has spent in total. 
    // Does not factor in returned items/orders.
    public static function findSpent(User $user): float
    {
        $spent = 0.00;

        $spent += Transactions::where('purchaser_id', $user->id)->sum('total_price');

        $activities = DB::table('activity_transactions')->where('user_id', $user->id)->get();
        foreach ($activities as $activity) {
            $spent += ($activity->activity_price * $activity->activity_gst); 
        }
        return number_format($spent, 2);
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
                    $returned += ($product['returned'] * $product['price'] * $tax);
                }
            }
        }

        $activity_transactions = DB::table('activity_transactions')->where([['user_id', $user->id], ['status', true]])->get();
        foreach($activity_transactions as $transaction) {
            $returned += ($transaction->activity_price * $transaction->activity_gst);
        }

        return number_format($returned, 2);
    }

    // Find how much money a user owes. 
    // Taking their amount spent and subtracting the amount they have returned.
    public static function findOwing(User $user): float
    {
        return number_format(self::findSpent($user) - self::findReturned($user), 2);
    }
}
