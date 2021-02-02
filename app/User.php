<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Rennokki\QueryCache\Traits\QueryCacheable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements CastsAttributes
{
    use QueryCacheable;

    protected $cacheFor = 180;
    protected $fillable = ['balance'];

    // TODO: Would using hasOne be better than casting?
    protected $casts = [
        'role' => Role::class
    ];

    public function get($model, string $key, $value, array $attributes)
    {
        return User::find($value);
    }

    public function set($model, string $key, $value, array $attributes)
    {
        // when creating an order an object is passed as $value
        if (is_object($value)) {
            return $value->id;
        }
        // but when returning an order, only their ID is passed as $value
        return $value;
    }

    public function hasPermission($permissions): bool
    {
        return $this->role->hasPermission($permissions);
    }

    private $_activity_transactions, $_transactions = null;
    
    // TODO: getViewUrl and getEditUrl update: why?

    private function getActivityTransactions(): Collection
    {
        if ($this->_activity_transactions == null) {
            $this->_activity_transactions = DB::table('activity_transactions')->where('user_id', $this->id)->get();
        }
        
        return $this->_activity_transactions;
    }

    private function getTransactions(): Collection
    {
        if ($this->_transactions == null) {
            $this->_transactions = Transaction::where('purchaser_id', $this->id)->get();
        }

        return $this->_transactions;
    }

    // Find how much a user has spent in total. 
    // Does not factor in returned items/orders.
    public function findSpent(): float
    {
        $spent = $this->getTransactions()->sum('total_price');

        $activities = $this->getActivityTransactions();
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

        $transactions = $this->getTransactions();
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

        $activity_transactions = $this->getActivityTransactions();
        foreach($activity_transactions as $transaction) {
            if ($transaction->status) {
                $returned += ($transaction->activity_price * $transaction->activity_gst);
            }
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
