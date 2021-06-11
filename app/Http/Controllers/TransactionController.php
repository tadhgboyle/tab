<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Helpers\SettingsHelper;
use App\Services\TransactionReturnService;
use App\Services\TransactionCreationService;

class TransactionController extends Controller
{
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

    public function view(int $transaction_id)
    {
        $transaction = Transaction::find($transaction_id);
        if ($transaction == null) {
            return redirect()->route('orders_list')->with('error', 'Invalid order.')->send();
        }

        $transaction_items = [];
        foreach (explode(', ', $transaction->products) as $product) {
            $transaction_items[] = ProductHelper::deserializeProduct($product);
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
