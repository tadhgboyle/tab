<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreationService;

class TransactionController extends Controller
{
    public function submit(Request $request)
    {
        return (new TransactionCreationService($request))->redirect();
    }

    public function returnTransaction(Transaction $transaction)
    {
        return (new TransactionReturnService($transaction))->return()->redirect();
    }

    public function returnItem(int $product_id, Transaction $transaction)
    {
        return (new TransactionReturnService($transaction))->returnItem($product_id)->redirect();
    }

    public function list()
    {
        return view('pages.orders.list', [
            'transactions' => Transaction::orderBy('created_at', 'DESC')->get(),
        ]);
    }

    public function view(Transaction $transaction)
    {
        return view('pages.orders.view', [
            'transaction' => $transaction,
            'transaction_items' => $transaction->products,
            'transaction_returned' => $transaction->getReturnStatus(),
        ]);
    }

    public function order(User $user, SettingsHelper $settingsHelper)
    {
        if (!hasPermission('cashier_self_purchases') && $user->id === auth()->id()) {
            return redirect('/')->with('error', 'You cannot make purchases for yourself.');
        }

        return view('pages.orders.order', [
            'user' => $user,
            'products' => Product::orderBy('name', 'ASC')->get(),
            'gst' => $settingsHelper->getGst(),
            'pst' => $settingsHelper->getPst(),
        ]);
    }
}
