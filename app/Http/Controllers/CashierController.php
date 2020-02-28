<?php

namespace App\Http\Controllers;

use App\Products;
use App\Transactions;
use Illuminate\Http\Request;

class CashierController extends Controller

{
    public function submit(Request $request)
    {
        if (isset($request->product)) {
            $products = array();
            $total_price = 0;
            foreach ($request->product as $product) {
                array_push($products, $product);
                $product_price = Products::select('price')->where('id', '=', $product)->get();
                $total_price += $product_price['0']['price'];
            }
            $transaction = new Transactions();
            $transaction->purchaser_id = $request->purchaser_id;
            $transaction->cashier_id = $request->cashier_id;
            $transaction->products = implode(", ", $products);
            $transaction->total_price = $total_price;
            $transaction->save();
            echo "products " . implode($products);
            echo "total price " . $total_price;
        } else {
            return redirect()->back()->withInput();
        }
    }
}
