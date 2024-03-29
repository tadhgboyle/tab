<?php

namespace App\Http\Controllers;

use App\Helpers\TaxHelper;
use App\Models\User;
use App\Models\Product;
use App\Helpers\Permission;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Models\TransactionProduct;
use Illuminate\Http\RedirectResponse;
use App\Services\Transactions\TransactionReturnService;
use App\Services\Transactions\TransactionCreateService;
use App\Services\Transactions\TransactionReturnProductService;

class TransactionController extends Controller
{
    public function index()
    {
        return view('pages.orders.list', [
            'transactions' => Transaction::orderBy('created_at', 'DESC')
                ->with('purchaser', 'cashier')
                ->withSum('products', 'quantity')
                ->get(),
        ]);
    }

    public function show(Transaction $transaction)
    {
        return view('pages.orders.view', [
            'transaction' => $transaction,
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
            'current_gst' => $settingsHelper->getGst() / 100,
            'current_pst' => $settingsHelper->getPst() / 100,
        ]);
    }

    public function store(Request $request, User $user): RedirectResponse
    {
        return (new TransactionCreateService($request, $user))->redirect();
    }

    public function returnTransaction(Transaction $transaction): RedirectResponse
    {
        return (new TransactionReturnService($transaction))->redirect();
    }

    public function returnProduct(Transaction $transaction, TransactionProduct $transactionProduct): RedirectResponse
    {
        return (new TransactionReturnProductService($transactionProduct))->redirect();
    }

    public function ajaxGetProducts(Transaction $transaction): string
    {
        $output = '';

        foreach ($transaction->products as $transactionProduct) {
            $output .=
                '<tr>' .
                    '<td>' . $transactionProduct->product->name . '</td>' .
                    '<td>' . $transactionProduct->category->name . '</td>' .
                    '<td>' . $transactionProduct->price . '</td>' .
                    '<td>' . $transactionProduct->quantity . '</td>' .
                '</tr>';
        }

        return $output;
    }
}
