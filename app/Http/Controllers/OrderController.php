<?php

namespace App\Http\Controllers;

use App\User;
use App\Products;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public static function deserializeProduct($product)
    {
        /**
         * Example serialized product:
         * 3*5$1.45G1.07P1.05R0
         * ID: 3
         * Quantity: 5
         * Price: 1.45 each
         * Gst: 1.07
         * Pst: 1.05
         * Returned: 0 / False
         * */
        $product_id = strtok($product, "*");
        $product_name = DB::table('products')->where('id', $product_id)->pluck('name')->first();
        $product_category = DB::table('products')->where('id', $product_id)->pluck('category')->first();
        $product_quantity = $product_price = $product_gst = $product_pst = $product_returned = 0.00;
        if (preg_match('/\*(.*?)\$/', $product, $match) == 1) {
            $product_quantity = $match[1];
        }
        if (preg_match('/\$(.*?)G/', $product, $match) == 1) {
            $product_price = $match[1];
        }
        if (preg_match('/G(.*?)P/', $product, $match) == 1) {
            $product_gst = $match[1];
        }
        if (preg_match('/P(.*?)r/', $product, $match) == 1) {
            $product_pst = $match[1];
        }
        $product_returned = substr($product, strpos($product, "R") + 1);
        return array(
            'id' => $product_id,
            'name' => $product_name,
            'category' => $product_category,
            'quantity' => $product_quantity,
            'price' => $product_price,
            'gst' => $product_gst,
            'pst' => $product_pst,
            'returned' => $product_returned
        );
    }

    public function submit(Request $request)
    {
        if (isset($request->product)) {
            $products = array();
            $transaction_categories = array();
            $total_price = 0;
            $quantity = 1;
            $product_quantity = 1;
            $pst_metadata = "";
            $total_tax = SettingsController::getGst();
            foreach ($request->product as $product) {
                $product_info = Products::select('name', 'price', 'category', 'pst')->where('id', $product)->get();
                if (array_key_exists($product, $request->quantity)) {
                    $quantity = $request->quantity[$product];
                    if ($quantity < 1) {
                        return redirect()->back()->withInput()->with('error', 'Quantity must be above 1 for item ' . $product_info['0']['name']);
                    }
                    if ($product_info['0']['pst'] == 1) {
                        $total_tax = ($total_tax + SettingsController::getPst()) - 1;
                        $pst_metadata = SettingsController::getPst();
                    } else {
                        $pst_metadata = "";
                    }
                    $product_quantity = $product . "*" . $quantity . "$" . $product_info['0']['price'] . "G" . SettingsController::getGst() . "P" . $pst_metadata . "R0";
                    if (!in_array($product_info['0']['category'], $transaction_categories)) array_push($transaction_categories, $product_info['0']['category']);
                }
                array_push($products, $product_quantity);
                $total_price += (($product_info['0']['price'] * $quantity) * $total_tax);
                $total_tax = SettingsController::getGst();
            }
            $purchaser_info = User::select('full_name', 'balance')->where('id', $request->purchaser_id)->get();
            $remaining_balance = $purchaser_info['0']['balance'] - $total_price;
            if ($remaining_balance < 0) {
                return redirect()->back()->withInput()->with('error', 'Not enough balance. ' . $purchaser_info['0']['full_name'] . " only has $" . $purchaser_info['0']['balance']);
            }
            $category_spent = 0.00;
            $category_limit = 0.00;
            foreach ($transaction_categories as $category) {
                $category_limit = UserLimitsController::findLimit($request->purchaser_id, $category);
                $category_spent = UserLimitsController::findSpent($request->purchaser_id, $category, UserLimitsController::findDuration($request->purchaser_id, $category));
                foreach ($products as $product) {
                    $product_info = OrderController::deserializeProduct($product);
                    if ($product_info['category'] = $category) {
                        $category_spent += ($product_info['price'] * $product_info['quantity']);
                    }
                }
                if ($category_spent >= $category_limit && $category_limit != -1) {
                    return redirect()->back()->with('error', 'Not enough balance in that category: ' . ucfirst($category) . '. Limit: $' . $category_limit . '.');
                    $category_spent = 0.00;
                    $category_limit = 0.00;
                }
            }
            DB::table('users')
                ->where('id', $request->purchaser_id)
                ->update(['balance' => $remaining_balance]);

            $transaction = new Transactions();
            $transaction->purchaser_id = $request->purchaser_id;
            $transaction->cashier_id = $request->cashier_id;
            $transaction->products = implode(", ", $products);
            $transaction->total_price = $total_price;
            $transaction->save();

            return redirect('/')->with('success', 'Order #' . $transaction->id . '. ' . $purchaser_info['0']['full_name'] . " now has $" . number_format(round($remaining_balance, 2), 2));
        } else {
            return redirect()->back()->withInput()->with('error', 'Please select at least one item.');
        }
    }

    public function return($id)
    {
        $total_tax = SettingsController::getGst();
        $order_info = Transactions::select('purchaser_id', 'products')->where('id', $id)->get();
        $purchaser_info = User::select('id', 'full_name', 'balance')->where('id', $order_info['0']['purchaser_id'])->get();
        $total_price = 0;
        foreach (explode(", ", $order_info['0']['products']) as $items) {
            $item_info = OrderController::deserializeProduct($items);
            if (DB::table('products')->where('id', $item_info['id'])->pluck('pst')->first() == 1) {
                $total_tax = ($total_tax + SettingsController::getPst()) - 1;
            }
            $total_price += ($item_info['price'] * $item_info['quantity']) * $total_tax;
            $total_tax = SettingsController::getGst();
        }
        DB::table('users')
            ->where('id', $purchaser_info['0']['id'])
            ->update(['balance' => ($purchaser_info['0']['balance'] + $total_price)]);
        DB::table('transactions')
            ->where('id', $id)
            ->update(['status' => '1']);
        return redirect('/orders')->with('success', 'Successfully returned order #' . $id . ' for ' . $purchaser_info['0']['full_name']);
    }
}
