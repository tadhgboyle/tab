<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Services\TransactionReturnService;
use App\Services\TransactionCreationService;

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
        return (new TransactionCreationService($request))->redirect();
    }

    public function returnTransaction(int $transaction_id)
    {
        return (new TransactionReturnService($transaction_id))->return()->redirect();
    }

    public function returnItem(int $item_id, int $transaction_id)
    {
        return (new TransactionReturnService($transaction_id))->returnItem($item_id)->redirect();
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
