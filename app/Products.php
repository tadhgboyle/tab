<?php

namespace App;

use App\Http\Controllers\OrderController;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    public static function findSold($product) {

        $sold = 0;

        foreach (Transactions::all() as $transaction) {
            foreach(explode(", ", $transaction->products) as $transaction_product) {
               if (OrderController::deserializeProduct($transaction_product)['id'] == $product) $sold++;
            }
        }

        return $sold;
    }
}
