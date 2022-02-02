<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ProductHelper;
use App\Helpers\SettingsHelper;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreationService;

class TransactionController extends Controller
{
    public function submit(Request $request): \Illuminate\Http\RedirectResponse
    {
        return (new TransactionCreationService($request))->redirect();
    }

    public function returnTransaction(int $transaction_id): \Illuminate\Http\RedirectResponse
    {
        return (new TransactionReturnService($transaction_id))->return()->redirect();
    }

    public function returnItem(int $item_id, int $transaction_id): \Illuminate\Http\RedirectResponse
    {
        return (new TransactionReturnService($transaction_id))->returnItem($item_id)->redirect();
    }

    public function list()
    {
        return view('pages.orders.list', [
            'transactions' => Transaction::orderBy('created_at', 'DESC')->get(),
        ]);
    }

    public function view(Transaction $transaction)
    {
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

    public function order(User $user)
    {
        if (!hasPermission('cashier_self_purchases')) {
            if ($user->id == auth()->id()) {
                return redirect('/')->with('error', 'You cannot make purchases for yourself.');
            }
        }

        return view('pages.orders.order', [
            'user' => $user,
            'products' => Product::orderBy('name', 'ASC')->get(),
            'gst' => SettingsHelper::getInstance()->getGst(),
            'pst' => SettingsHelper::getInstance()->getPst(),
        ]);
    }
}
