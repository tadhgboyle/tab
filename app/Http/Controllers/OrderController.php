<?php

namespace App\Http\Controllers;

use App\User;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public static function checkReturned(Transaction $order): int
    {
        $products_returned = 0;
        $order_products = 0;
        if ($order->status) {
            return 1;
        } else {
            foreach (explode(", ", $order->products) as $product) {
                $product_info = self::deserializeProduct($product);
                if ($product_info['returned'] >= $product_info['quantity']) {
                    $products_returned++;
                } else if ($product_info['returned'] > 0) {
                    // semi returned if at least one product has a returned value
                    return 2;
                }
                $order_products++;
            }
            if ($products_returned >= $order_products) {
                $order->update(['status' => '1']);
                return 1;
            }

            return 0;
        }
    }

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
    private static function serializeProduct($id, $quantity, $price, $gst, $pst, $returned): string
    {
        return $id . "*" . $quantity . "$" . $price . "G" . $gst . "P" . $pst . "R" . $returned;
    }
    
    /**
     * Example serialized product:
     * 3*5$1.45G1.07P1.05R0
     * ID: 3
     * Quantity: 5
     * Price: 1.45 each
     * Gst: 1.07
     * Pst: 1.05
     * Returned: Quantity returned -- Default 0
     */
    public static function deserializeProduct(string $product): array
    {
        $product_id = strtok($product, "*");
        $product_object = Product::find($product_id);
        $product_name = $product_object->name;
        $product_category = $product_object->category;
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
        // TODO: Make this a permission
        // if (SettingsController::getSelfPurchases() && $request->cashier_id == $request->purchaser_id && User::find($request->cashier_id)->role != 'administrator') {
        //     return redirect('/')->with('error', 'You cannot make purchases for yourself.');
        // }

        if (isset($request->product)) {

            $products = $transaction_categories = $stock_products = array();
            $total_price = 0;
            $quantity = 1;
            $product_metadata = $pst_metadata = "";
            $total_tax = SettingsController::getGst();

            // For some reason, old() does not seem to work with array[] inputs in form. This will do for now... 
            // ^ I'm sure its a silly mistake on my end
            foreach ($request->product as $product) {
                session()->flash('quantity[' . $product . ']', $request->quantity[$product]);
                session()->flash('product[' . $product . ']', true);
            }

            // Loop each product. Serialize it, and add it's cost to the transaction total
            foreach ($request->product as $product) {
                $product_info = Product::find($product);
                if (array_key_exists($product, $request->quantity)) {
                    $quantity = $request->quantity[$product];
                    if ($quantity < 1) {
                        return redirect()->back()->withInput()->with('error', 'Quantity must be >= 1 for item ' . $product_info->name);
                    }

                    // Stock handling
                    if (!$product_info->hasStock($quantity)) {
                        return redirect()->back()->withInput()->with('error', 'Not enough ' . $product_info->name . ' in stock. Only ' . $product->stock . ' remaining.');
                    } else {
                        array_push($stock_products, $product_info);
                    }

                    if ($product_info->pst == true) {
                        $total_tax = ($total_tax + SettingsController::getPst()) - 1;
                        $pst_metadata = SettingsController::getPst();
                    } else {
                        $pst_metadata = "null";
                    }

                    if (!in_array($product_info->category, $transaction_categories)) {
                        array_push($transaction_categories, $product_info->category);
                    }

                    $product_metadata = self::serializeProduct($product, $quantity, $product_info->price, SettingsController::getGst(), $pst_metadata, 0);
                }

                array_push($products, $product_metadata);
                $total_price += (($product_info->price * $quantity) * $total_tax);
                $total_tax = SettingsController::getGst();
            }

            $purchaser = User::find($request->purchaser_id);
            $remaining_balance = $purchaser->balance - $total_price;
            if ($remaining_balance < 0) {
                return redirect()->back()->withInput()->with('error', 'Not enough balance. ' . $purchaser->full_name . " only has $" . $purchaser->balance);
            }

            $category_spent = $category_limit = 0.00;
            // Loop categories within this transaction
            foreach ($transaction_categories as $category) {
                $limit_info = UserLimitsController::getInfo($request->purchaser_id, $category);
                $category_limit = $limit_info->limit_per;
                // Skip this category if they have unlimited. Saves time querying
                if ($category_limit == -1) continue;
                $category_spent = $category_spent_orig = UserLimitsController::findSpent($request->purchaser_id, $category, $limit_info);

                // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
                foreach ($products as $product) {
                    $product_metadata = self::deserializeProduct($product);
                    if ($product_metadata['category'] == $category) {
                        $tax_percent = $product_metadata['gst'];
                        if ($product_metadata['pst'] != "null") $tax_percent += $product_metadata['pst'] - 1;
                        $category_spent += ($product_metadata['price'] * $product_metadata['quantity']) * $tax_percent;
                    }
                }
                // Break loop if we exceed their limit
                if ($category_spent >= $category_limit) {
                    return redirect()->back()->withInput()->with('error', 'Not enough balance in that category: ' . ucfirst($category) . ' (Limit: $' . $category_limit . ', Remaining: $' . number_format($category_limit - $category_spent_orig, 2) . ').');
                }
            }

            // Stock handling
            foreach ($stock_products as $product) {
                $product->removeStock($request->quantity[$product->id], false);
            }

            // Update their balance
            $purchaser->update(['balance' => $remaining_balance]);

            // Save transaction in database
            $transaction = new Transaction();
            $transaction->purchaser_id = $purchaser->id;
            $transaction->cashier_id = $request->cashier_id;
            $transaction->products = implode(", ", $products);
            $transaction->total_price = $total_price;
            $transaction->save();

            return redirect('/')->with('success', 'Order #' . $transaction->id . '. ' . $purchaser->full_name . " now has $" . number_format(round($remaining_balance, 2), 2));
        } else {
            return redirect()->back()->withInput()->with('error', 'Please select at least one item.');
        }
    }

    public function returnOrder($id)
    {
        $order_info = Transaction::find($id);
        // This should never happen, but a good security measure
        if (self::checkReturned($order_info) == 1) return redirect()->back()->with('error', 'That order has already been fully returned.');

        $total_tax = $total_price = 0;
        $purchaser = $order_info->purchaser_id;

        // Loop through products from the order and deserialize them to get their prices & taxes etc when they were purchased
        foreach (explode(", ", $order_info->products) as $product) {
            $product_metadata = self::deserializeProduct($product);
            echo $product_metadata['pst'];
            if ($product_metadata['pst'] == "null") {
                $total_tax = $product_metadata['gst'];
            } else {
                $total_tax = ($product_metadata['gst'] + $product_metadata['pst']) - 1;
            }
            $total_price += ($product_metadata['price'] * $product_metadata['quantity']) * $total_tax;
        }

        // Update their balance and set the status to 1 for the returned order
        $purchaser->update(['balance' => ($purchaser->balance + $total_price)]);
        $order_info->update(['status' => 1]);
        return redirect()->back()->with('success', 'Successfully returned order #' . $id . ' for ' . $purchaser->full_name);
    }

    public function returnItem($item, $order)
    {
        $order_info = Transaction::find($order);
        // this shouldnt happen, but worth a check
        if (self::checkReturned($order_info) == 1) {
            return redirect()->back()->with('error', 'That order has already been returned, so you cannot return an item from it.');
        }

        $user = $order_info->purchaser_id;
        $user_balance = $user->balance;
        $found = false;

        // Loop order products until we find the matching id
        foreach (explode(", ", $order_info->products) as $order_products) {
            $order_product = self::deserializeProduct($order_products);
            $total_tax = 0.00;

            // Only proceed if this is the requested item id
            if ($order_product['id'] == $item) {
                $found = true;

                // If it has not been returned more times than it was purchased, then ++ the returned count and refund the original cost + taxes
                if ($order_product['returned'] < $order_product['quantity']) {
                    $order_product['returned']++;

                    // Check taxes and apply correct %
                    if ($order_product['pst'] == "null") {
                        $total_tax = $order_product['gst'];
                    } else {
                        $total_tax = (($order_product['pst'] + $order_product['gst']) - 1);
                    }

                    // Gets funky now. find the exact string of the original item from the string of order items and replace with the new string where the R value is ++
                    $updated_products = str_replace(
                        $order_products,
                        self::serializeProduct($order_product['id'], $order_product['quantity'], $order_product['price'], $order_product['gst'], $order_product['pst'], $order_product['returned']),
                        explode(", ", $order_info->products)
                    );
                    // Update their balance
                    $user->update(['balance' => $user_balance += ($order_product['price'] * $total_tax)]);
                    // Now insert the funky replaced string where it was originally
                    $order_info->update(['products' => implode(", ", $updated_products)]);
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
