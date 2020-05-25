<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Products extends Model
{
    public static function findSold($product, $lookBack) {

        $sold = 0;

        foreach (Transactions::where('created_at', '>=', Carbon::now()->subDays($lookBack)->toDateTimeString())->get() as $transaction) {
            foreach(explode(", ", $transaction->products) as $transaction_product) {
               if (OrderController::deserializeProduct($transaction_product)['id'] == $product) $sold++;
            }
        }

        return $sold;
    }
}
