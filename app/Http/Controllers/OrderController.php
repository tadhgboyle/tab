<?php

namespace App\Http\Controllers;

use App\User;
use App\Products;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public static function serializeProduct($id, $quantity, $price, $gst, $pst, $returned)
    {
        /**
         * Example deserialized input:
         * ID: 34
         * Quantity: 2
         * Price: 1.45 each
         * GST: 1.08
         * PST: 1.04
         * Returned: 1
         * 
         * Example Output: 34*2$1.45G1.08P1.04R1
         */
        return $id . "*" . $quantity . "$" . $price . "G" . $gst . "P" . $pst . "R" . $returned;
    }

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
         * Returned: Quantity returned -- Default 0
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
                    if ($product_info['category'] == $category) {
                        $category_spent += ($product_info['price'] * $product_info['quantity']);
                    }
                }
                if ($category_spent >= $category_limit && $category_limit != -1) {
                    return redirect()->back()->with('error', 'Not enough balance in that category: ' . ucfirst($category) . ', limit: $' . $category_limit . '.');
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

    public function returnOrder($id)
    {
        $total_tax = SettingsController::getGst();
        $order_info = Transactions::select('purchaser_id', 'products', 'status')->where('id', $id)->get();
        if ($order_info['0']['status'] == "1") {
            return redirect()->back()->with('error', 'That order has already been returned.');
        }
        $purchaser_info = User::select('id', 'full_name', 'balance')->where('id', $order_info['0']['purchaser_id'])->get();
        $total_price = 0;
        $returned_count = 0;
        $product_count = 0;
        foreach (explode(", ", $order_info['0']['products']) as $items) {
            $item_info = OrderController::deserializeProduct($items);
            // check if all order items have been fully returned
            $product_count++;
            if ($item_info['returned'] >= $item_info['quantity']) $returned_count++;
            if (DB::table('products')->where('id', $item_info['id'])->pluck('pst')->first() == 1) {
                $total_tax = ($total_tax + SettingsController::getPst()) - 1;
            }
            $total_price += ($item_info['price'] * $item_info['quantity']) * $total_tax;
            $total_tax = SettingsController::getGst();
        }
        if ($returned_count >= $product_count) {
            if ($order_info['0']['status'] == 1)
            return redirect()->back()->with('error', 'That order has already been fully returned.' . $returned_count . ' ' . count((array) $order_info['0']['products']));
        }
        DB::table('users')
            ->where('id', $purchaser_info['0']['id'])
            ->update(['balance' => ($purchaser_info['0']['balance'] + $total_price)]);
        DB::table('transactions')
            ->where('id', $id)
            ->update(['status' => '1']);
        return redirect()->back()->with('success', 'Successfully returned order #' . $id . ' for ' . $purchaser_info['0']['full_name']);
    }

    public function returnItem($item, $order)
    {
        $order_info = Transactions::select('id', 'purchaser_id', 'products', 'status')->where('id', $order)->get();
        if ($order_info['0']['status'] == "1") {
            return redirect()->back()->with('error', 'That order has already been returned, so you cannot return an item from it.');
        }
        foreach (explode(", ", $order_info['0']['products']) as $order_products) {
            $order_product = OrderController::deserializeProduct($order_products);
            $total_tax = 0.00;
            if ($order_product['id'] == $item) {
                if ($order_product['returned'] < $order_product['quantity']) {
                    $order_product['returned']++;
                    if ($order_product['pst'] = 0) $total_tax = $order_product['gst'];
                    else $total_tax = (($order_product['pst'] + $order_product['gst']) - 1);
                    $updated_products = str_replace(
                        $order_products,
                        OrderController::serializeProduct($order_product['id'], $order_product['quantity'], $order_product['price'], $order_product['gst'], $order_product['pst'], $order_product['returned']),
                        explode(", ", $order_info['0']['products'])
                    );
                    print_r($updated_products);
                    DB::table('users')
                        ->where('id', $order_info['0']['purchaser_id'])
                        ->update(['balance' => ($order_product['price'] * $total_tax)]);
                } else {
                    return redirect()->back()->with('error', 'That item has already been returned the maximum amount of times for that order.');
                }
            }
            // ignore
        }
        // store all products, update the one we need and update in mysql
        DB::table('transactions')
            ->where('id', $order_info['0']['id'])
            ->update(['products' => implode(", ", $updated_products)]);
        return redirect()->back()->with('success', 'Successfully returned x1 ' . $order_product['name'] . '.');
    }
}
