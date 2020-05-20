<?php

namespace App\Http\Controllers;

use App\User;
use App\Products;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public static function checkReturned($order)
    {
        $order_info = Transactions::select('products', 'status')->where('id', $order)->get();
        $products_returned = 0;
        $order_products = 0;
        if ($order_info['0']['status'] == "1") return true;
        else {
            foreach (explode(", ", $order_info) as $product) {
                $product_info = OrderController::deserializeProduct($product);
                if ($product_info['returned'] >= $product_info['quantity']) {
                    $products_returned++;
                }
                $order_products++;
            }
            if ($products_returned >= $order_products) {
                DB::table('transactions')
                    ->where('id', $order)
                    ->update(['status' => '1']);
                return true;
            } else return false;
        }
    }

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
        // Quantity
        if (preg_match('/\*(.*?)\$/', $product, $match) == 1) {
            $product_quantity = $match[1];
        }
        // Price
        if (preg_match('/\$(.*?)G/', $product, $match) == 1) {
            $product_price = $match[1];
        }
        // Gst 
        if (preg_match('/G(.*?)P/', $product, $match) == 1) {
            $product_gst = $match[1];
        }
        // Pst 
        if (preg_match('/P(.*?)R/', $product, $match) == 1) {
            $product_pst = $match[1];
        }
        // Returned
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

            $products = $transaction_categories = array();
            $total_price = 0;
            $quantity = 1;
            $product_metadata = $pst_metadata = "";
            $total_tax = SettingsController::getGst();

            // Loop each product. Serialize it, and add it's cost to the transaction total
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
                        $pst_metadata = "null";
                    }
                    $product_metadata = $product . "*" . $quantity . "$" . $product_info['0']['price'] . "G" . SettingsController::getGst() . "P" . $pst_metadata . "R0";
                    if (!in_array($product_info['0']['category'], $transaction_categories)) array_push($transaction_categories, $product_info['0']['category']);
                }
                array_push($products, $product_metadata);
                $total_price += (($product_info['0']['price'] * $quantity) * $total_tax);
                $total_tax = SettingsController::getGst();
            }

            $purchaser_info = User::select('full_name', 'balance')->where('id', $request->purchaser_id)->get();
            $remaining_balance = $purchaser_info['0']['balance'] - $total_price;
            if ($remaining_balance < 0) {
                return redirect()->back()->withInput()->with('error', 'Not enough balance. ' . $purchaser_info['0']['full_name'] . " only has $" . $purchaser_info['0']['balance']);
            }

            $category_spent = $category_limit = 0.00;
            // Loop categories within this transaction
            foreach ($transaction_categories as $category) {
                $category_limit = UserLimitsController::findLimit($request->purchaser_id, $category);
                // Skip this category if they have unlimited. Saves time querying
                if ($category_limit == -1) continue;
                $category_spent = $category_spent_orig = UserLimitsController::findSpent($request->purchaser_id, $category, UserLimitsController::findDuration($request->purchaser_id, $category));
                // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
                foreach ($products as $product) {
                    $product_metadata = OrderController::deserializeProduct($product);
                    if ($product_metadata['category'] == $category) {
                        $category_spent += ($product_metadata['price'] * $product_metadata['quantity']);
                    }
                }
                // Break loop if we exceed their limit
                if ($category_spent >= $category_limit) {
                    return redirect()->back()->with('error', 'Not enough balance in that category: ' . ucfirst($category) . ' (Limit: $' . $category_limit . ', Remaining: $' . ($category_limit - number_format($category_spent_orig, 2)) . ').');
                }
            }
            // Update their balance
            DB::table('users')
                ->where('id', $request->purchaser_id)
                ->update(['balance' => $remaining_balance]);

            // Save transaction in database
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
        // This should never happen, but a good security measure
        if (OrderController::checkReturned($id)) return redirect()->back()->with('error', 'That order has already been fully returned.');

        $total_tax = $total_price = 0;
        $order_info = Transactions::select('purchaser_id', 'products', 'status')->where('id', $id)->get();
        $purchaser_info = User::select('id', 'full_name', 'balance')->where('id', $order_info['0']['purchaser_id'])->get();

        // Loop through products from the order and deserialize them to get their prices & taxes etc when they were purchased
        foreach (explode(", ", $order_info['0']['products']) as $product) {
            $product_metadata = OrderController::deserializeProduct($product);
            echo $product_metadata['pst'];
            if ($product_metadata['pst'] == "null") {
                $total_tax = $product_metadata['gst'];
            } else {
                $total_tax = ($product_metadata['gst'] + $product_metadata['pst']) - 1;
            }
            $total_price += ($product_metadata['price'] * $product_metadata['quantity']) * $total_tax;
        }

        // Update their balance and set the status to 1 for the returned order
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
        // this shouldnt happen, but worth a check.
        if (OrderController::checkReturned($order)) return redirect()->back()->with('error', 'That order has already been returned, so you cannot return an item from it.');

        $order_info = Transactions::select('id', 'purchaser_id', 'products')->where('id', $order)->get();
        $user_balance = User::where('id', $order_info['0']['purchaser_id'])->pluck('balance')->first();
        $found = false;

        // Loop order products until we find the matching id
        foreach (explode(", ", $order_info['0']['products']) as $order_products) {
            $order_product = OrderController::deserializeProduct($order_products);
            $total_tax = 0.00;

            // Only proceed if this is the requested item id
            if ($order_product['id'] == $item) {
                $found == true;

                // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
                if ($order_product['returned'] < $order_product['quantity']) {
                    $order_product['returned']++;

                    // Check taxes and apply correct %
                    if ($order_product['pst'] == "null") $total_tax = $order_product['gst'];
                    else $total_tax = (($order_product['pst'] + $order_product['gst']) - 1);

                    // Gets funky now. find the exact string of the original item from the string of order items and replace with the new string where the R value is ++
                    $updated_products = str_replace(
                        $order_products,
                        OrderController::serializeProduct($order_product['id'], $order_product['quantity'], $order_product['price'], $order_product['gst'], $order_product['pst'], $order_product['returned']),
                        explode(", ", $order_info['0']['products'])
                    );
                    // Update their balance
                    DB::table('users')
                        ->where('id', $order_info['0']['purchaser_id'])
                        ->update(['balance' => $user_balance += ($order_product['price'] * $total_tax)]);
                    // Now insert the funky replaced string where it was originally
                    DB::table('transactions')
                        ->where('id', $order_info['0']['id'])
                        ->update(['products' => implode(", ", $updated_products)]);
                    return redirect()->back()->with('success', 'Successfully returned x1 ' . $order_product['name'] . ' for order #' . $order . '.');
                } else {
                    return redirect()->back()->with('error', 'That item has already been returned the maximum amount of times for that order.');
                }
            }
        }
        if ($found === false) {
            return redirect()->back()->with('error', 'That item was not in the original order.');
        }
    }
}
