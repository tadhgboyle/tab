<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements CastsAttributes
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['balance'];

    // TODO: Would using hasOne be better than casting?
    protected $casts = [
        'role' => Roles::class
    ];

    public function get($model, string $key, $value, array $attributes)
    {
        return User::find($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        return $value;
    }

    public function hasPermission($permissions): bool
    {
        return $this->role->hasPermission($permissions);
    }
    
    // TODO: getViewUrl and getEditUrl update: why?

    // Find how much a user has spent in total. 
    // Does not factor in returned items/orders.
    public function findSpent(): float
    {
        $spent = Transactions::where('purchaser_id', $this->id)->sum('total_price');

        $activities = DB::table('activity_transactions')->where('user_id', $this->id)->get();
        foreach ($activities as $activity) {
            $spent += ($activity->activity_price * $activity->activity_gst); 
        }
        return number_format($spent, 2);
    }

    // Find how much a user has returned in total.
    // This will see if a whole order has been returned, or if not, check all items in an unreturned order.
    public function findReturned(): float
    {
        $returned = 0.00;

        $transactions = Transactions::where('purchaser_id', $this->id)->get();
        foreach ($transactions as $transaction) {
            if ($transaction->status == 1) {
                $returned += $transaction->total_price;
                continue;
            }

            foreach (explode(", ", $transaction->products) as $transaction_product) {
                $product = OrderController::deserializeProduct($transaction_product);
                if ($product['returned'] > 0) {
                    $tax = $product['gst'];
                    if ($product['pst'] != "null") {
                        $tax += ($product['pst'] - 1);
                    }
                    $returned += ($product['returned'] * $product['price'] * $tax);
                }
            }
        }

        $activity_transactions = DB::table('activity_transactions')->where([['user_id', $this->id], ['status', true]])->get();
        foreach($activity_transactions as $transaction) {
            $returned += ($transaction->activity_price * $transaction->activity_gst);
        }

        return number_format($returned, 2);
    }

    // Find how much money a user owes. 
    // Taking their amount spent and subtracting the amount they have returned.
    public function findOwing(): float
    {
        return number_format($this->findSpent() - $this->findReturned(), 2);
    }
}
