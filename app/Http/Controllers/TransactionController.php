<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;

class TransactionController extends Controller
{
    /**
     * Example deserialized input:
     * ID: 34
     * Quantity: 2
     * Price: 1.45 each
     * GST: 1.08
     * PST: 1.04
     * Returned: 1.
     *
     * Example Output: 34*2$1.45G1.08P1.04R1
     */
    public static function serializeProduct($id, $quantity, $price, $gst, $pst, $returned): string
    {
        return $id . '*' . $quantity . '$' . $price . 'G' . $gst . 'P' . $pst . 'R' . $returned;
    }

    /**
     * Example serialized product:
     * 3*5$1.45G1.07P1.05R0
     * ID: 3
     * Quantity: 5
     * Price: 1.45 each
     * Gst: 1.07
     * Pst: 1.05
     * Returned: Quantity returned -- Default 0.
     */
    // TODO: Create App\Helpers\ProductHelper and move this + serializer to it
    public static function deserializeProduct(string $product, bool $full = true): array
    {
        $product_id = strtok($product, '*');

        if ($full) {
            $product_object = Product::findOrFail($product_id);
            $product_name = $product_object->name;
            $product_category = $product_object->category_id;
        }

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

        if (preg_match('/P(.*?)R/', $product, $match) == 1) {
            $product_pst = $match[1];
        }

        $product_returned = substr($product, strpos($product, 'R') + 1);

        $return = [
            'id' => $product_id,
            'name' => $product_name ?? '',
            'category' => $product_category ?? '',
            'quantity' => $product_quantity,
            'price' => $product_price,
            'gst' => $product_gst,
            'pst' => $product_pst,
            'returned' => $product_returned,
        ];

        return $return;
    }

    public function submit(Request $request)
    {
        if (!hasPermission('cashier_self_purchases')) {
            if ($request->purchaser_id == auth()->id()) {
                return redirect('/')->with('error', 'You cannot make purchases for yourself.');
            }
        }

        if (!isset($request->product)) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one item.');
        }

        // For some reason, old() does not seem to work with array[] inputs in form. This will do for now...
        // ^ I'm sure its a silly mistake on my end
        foreach ($request->product as $product) {
            session()->flash("quantity[{$product}]", $request->quantity[$product]);
            session()->flash("product[{$product}]", true);
        }

        $transaction_products = $transaction_categories = $stock_products = [];
        $total_price = 0;
        $quantity = 1;
        $product_metadata = $pst_metadata = '';
        $total_tax = SettingsHelper::getInstance()->getGst();

        // Loop each product. Serialize it, and add it's cost to the transaction total
        foreach ($request->product as $product_id) {
            $product = Product::find($product_id);
            if (array_key_exists($product_id, $request->quantity)) {
                $quantity = $request->quantity[$product_id];
                if ($quantity < 1) {
                    return redirect()->back()->withInput()->with('error', 'Quantity must be >= 1 for item ' . $product->name);
                }

                // Stock handling
                if (!$product->hasStock($quantity)) {
                    return redirect()->back()->withInput()->with('error', 'Not enough ' . $product->name . ' in stock. Only ' . $product->stock . ' remaining.');
                }

                $stock_products[] = $product;

                if ($product->pst) {
                    $total_tax = ($total_tax + SettingsHelper::getInstance()->getPst()) - 1;
                    $pst_metadata = SettingsHelper::getInstance()->getPst();
                } else {
                    $pst_metadata = 'null';
                }

                // keep track of which unique categories are included in this transaction
                if (!in_array($product->category_id, $transaction_categories)) {
                    $transaction_categories[] = $product->category_id;
                }

                $product_metadata = self::serializeProduct($product_id, $quantity, $product->price, SettingsHelper::getInstance()->getGst(), $pst_metadata, 0);
            }

            $transaction_products[] = $product_metadata;
            $total_price += (($product->price * $quantity) * $total_tax);
            $total_tax = SettingsHelper::getInstance()->getGst();
        }

        $purchaser = User::find($request->purchaser_id);
        $remaining_balance = $purchaser->balance - $total_price;
        if ($remaining_balance < 0) {
            return redirect()->back()->withInput()->with('error', 'Not enough balance. ' . $purchaser->full_name . ' only has $' . $purchaser->balance);
        }

        $category_spent = $category_limit = 0.00;
        // Loop categories within this transaction
        foreach ($transaction_categories as $category_id) {

            $limit_info = UserLimitsHelper::getInfo($purchaser, $category_id);
            $category_limit = $limit_info->limit_per;

            // Skip this category if they have unlimited. Saves time querying
            if ($category_limit == -1) {
                continue;
            }

            $category_spent = $category_spent_orig = UserLimitsHelper::findSpent($purchaser, $category_id, $limit_info);

            // Loop all products in this transaction. If the product's category is the current one in the above loop, add it's price to category spent
            foreach ($transaction_products as $product) {

                $product_metadata = self::deserializeProduct($product);

                if ($product_metadata['category'] != $category_id) {
                    continue;
                }

                $tax_percent = $product_metadata['gst'];

                if ($product_metadata['pst'] != 'null') {
                    $tax_percent += $product_metadata['pst'] - 1;
                }

                $category_spent += ($product_metadata['price'] * $product_metadata['quantity']) * $tax_percent;
            }

            // Break loop if we exceed their limit
            if (!UserLimitsHelper::canSpend($purchaser, $category_spent, $category_id, $limit_info)) {
                return redirect()->back()->withInput()->with('error', 'Not enough balance in that category: ' . Category::find($category_id)->name . ' (Limit: $' . number_format($category_limit, 2) . ', Remaining: $' . number_format($category_limit - $category_spent_orig, 2) . ').');
            }
        }

        // Stock handling
        foreach ($stock_products as $product) {
            // we already know the product has stock via hasStock() call above, so we dont need to check for the result of removeStock()
            $product->removeStock($request->quantity[$product->id]);
        }

        // Update their balance
        $purchaser->update(['balance' => $remaining_balance]);

        // Save transaction in database
        $transaction = new Transaction();
        $transaction->purchaser_id = $purchaser->id;
        $transaction->cashier_id = auth()->id();
        $transaction->products = implode(', ', $transaction_products);
        $transaction->total_price = $total_price;
        $transaction->save();

        return redirect('/')->with('success', 'Order #' . $transaction->id . '. ' . $purchaser->full_name . ' now has $' . number_format(round($remaining_balance, 2), 2));
    }

    public function returnOrder(int $id)
    {
        $transaction = Transaction::find($id);

        if ($transaction == null) {
            return redirect()->back()->with('error', 'No transaction found with that ID.');
        }

        return $transaction->return();
    }

    public function returnItem(int $item_id, int $order_id)
    {
        $transaction = Transaction::find($order_id);

        if ($transaction == null) {
            return redirect()->back()->with('error', 'No transaction found with that ID.');
        }

        return $transaction->returnItem($item_id);
    }

    public function list()
    {
        return view('pages.orders.list', [
            'transactions' => Transaction::orderBy('created_at', 'DESC')->get(),
        ]);
    }

    public function view()
    {
        $transaction = Transaction::find(request()->route('id'));
        if ($transaction == null) {
            return redirect()->route('orders_list')->with('error', 'Invalid order.')->send();
        }

        $transaction_items = [];
        foreach (explode(', ', $transaction->products) as $product) {
            $transaction_items[] = self::deserializeProduct($product);
        }

        return view('pages.orders.view', [
            'transaction' => $transaction,
            'transaction_items' => $transaction_items,
            'transaction_returned' => $transaction->getReturnStatus(),
        ]);
    }

    public function order()
    {
        $user = User::find(request()->route('id'));
        if ($user == null) {
            return redirect()->route('index')->with('error', 'Invalid user.')->send();
        }

        if (!hasPermission('cashier_self_purchases')) {
            if ($user->id == auth()->id()) {
                return redirect('/')->with('error', 'You cannot make purchases for yourself.');
            }
        }

        return view('pages.orders.order', [
            'user' => $user,
            'products' => Product::orderBy('name', 'ASC')->where('deleted', false)->get(),
            'gst' => SettingsHelper::getInstance()->getGst(),
            'pst' => SettingsHelper::getInstance()->getPst(),
        ]);
    }
}
