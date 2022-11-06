<?php

namespace App\Http\Controllers;

use App\Helpers\Permission;
use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use Illuminate\Http\RedirectResponse;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreationService;

class TransactionController extends Controller
{
    public function index()
    {
        return view('pages.orders.list', [
            'transactions' => Transaction::orderBy('created_at', 'DESC')->get(),
        ]);
    }

    public function show(Transaction $transaction)
    {
        return view('pages.orders.view', [
            'transaction' => $transaction,
            'transaction_items' => $transaction->products,
            'transaction_returned' => $transaction->getReturnStatus(),
        ]);
    }

    public function create(User $user, SettingsHelper $settingsHelper)
    {
        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $user->id === auth()->id()) {
            return redirect('/')->with('error', 'You cannot make purchases for yourself.');
        }

        return view('pages.orders.order', [
            'user' => $user,
            'products' => Product::orderBy('name', 'ASC')->get(),
            'gst' => $settingsHelper->getGst(),
            'pst' => $settingsHelper->getPst(),
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        return (new TransactionCreationService($request, $user))->redirect();
    }

    public function returnTransaction(Transaction $transaction): RedirectResponse
    {
        return (new TransactionReturnService($transaction))->return()->redirect();
    }

    public function returnProduct(Transaction $transaction, Product $product): RedirectResponse
    {
        return (new TransactionReturnService($transaction))->returnItem($product)->redirect();
    }
}
