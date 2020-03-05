<?php

namespace App\Http\Controllers;

use App\User;
use App\Products;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function submit(Request $request)
    {
        if (isset($request->product)) {
            print_r($request->all());
            $products = array();
            $total_price = 0;
            $quantity = 1;
            $product_quantity = 1;
            $total_tax = SettingsController::getGst();
            foreach ($request->product as $product) {
                $product_info = Products::select('name', 'price', 'pst')->where('id', '=', $product)->get();
                if (array_key_exists($product, $request->quantity)) {
                    $quantity = $request->quantity[$product];
                    if ($quantity == 0) {
                        return redirect()->back()->withInput()->with('error', 'Quantity must be above or below 0 for item ' . $product_info['0']['name']);
                    }
                    $product_quantity = $product . "*" . $quantity;
                }
                if ($product_info['0']['pst'] == 1) {
                    $total_tax = ($total_tax + SettingsController::getPst()) - 1;
                }
                array_push($products, $product_quantity);
                $total_price += (($product_info['0']['price'] * $quantity) * $total_tax);
                $total_tax = SettingsController::getGst();
            }
            $purchaser_info = User::select('full_name', 'balance')->where('id', '=', $request->purchaser_id)->get();
            $remaining_balance = $purchaser_info['0']['balance'] - $total_price;
            if ($remaining_balance < 0) {
                return redirect()->back()->withInput()->with('error', 'Not enough balance. ' . $purchaser_info['0']['full_name'] . " only has $" . $purchaser_info['0']['balance']);
            } else {
                DB::table('users')
                    ->where('id', $request->purchaser_id)
                    ->update(['balance' => $remaining_balance]);
            }
            $transaction = new Transactions();
            $transaction->purchaser_id = $request->purchaser_id;
            $transaction->cashier_id = $request->cashier_id;
            $transaction->products = implode(", ", $products);
            $transaction->total_price = $total_price;
            $transaction->save();

            return redirect('/')->with('success', 'Order #' . $transaction->id . '. ' . $purchaser_info['0']['full_name'] . " now has $" . round($remaining_balance, 2));
        } else {
            return redirect()->back()->withInput()->with('error', 'Please select at least one item.');
        }
    }
    public function return($id)
    {
        $total_tax = SettingsController::getGst();
        $order_info = Transactions::select('purchaser_id', 'products')->where('id', '=', $id)->get();
        $purchaser_info = User::select('id', 'full_name', 'balance')->where('id', '=', $order_info['0']['purchaser_id'])->get();
        $total_price = 0;
        foreach (explode(", ", $order_info['0']['products']) as $items) {
            $item = strtok($items, "*");
            $quantity = substr($items, strpos($items, "*") + 1);
            $item_info = Products::select('price', 'pst')->where('id', '=', $item)->get();
            $item_price = $item_info['0']['price'];
            if ($item_info['0']['pst'] == 1) {
                $total_tax = ($total_tax + SettingsController::getPst()) - 1;
            }
            $total_price += ($item_price * $quantity) * $total_tax;
            $total_tax = SettingsController::getGst();
        }
        DB::table('users')
            ->where('id', $purchaser_info['0']['id'])
            ->update(['balance' => ($purchaser_info['0']['balance'] + $total_price)]);
        DB::table('transactions')
            ->where('id', $id)
            ->update(['status' => '1']);
        return redirect('/orders')->with('success', 'Successfully returned order ' . $id . ' for ' . $purchaser_info['0']['full_name']);
    }
}
