<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use App\Helpers\Permission;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Models\OrderProduct;
use Illuminate\Http\RedirectResponse;
use App\Services\Orders\OrderCreateService;
use App\Services\Orders\OrderReturnService;
use App\Services\Orders\OrderReturnProductService;

class OrderController extends Controller
{
    public function index()
    {
        return view('pages.orders.list', [
            'orders' => Order::orderBy('created_at', 'DESC')
                ->with('purchaser', 'cashier')
                ->withSum('products', 'quantity')
                ->get(),
        ]);
    }

    public function show(Order $order)
    {
        return view('pages.orders.view', [
            'order' => $order,
        ]);
    }

    public function create(User $user, SettingsHelper $settingsHelper)
    {
        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $user->id === auth()->id()) {
            return redirect()->route('cashier')->with('error', 'You cannot make purchases for yourself.');
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
        return (new OrderCreateService($request, $user))->redirect();
    }

    public function returnOrder(Order $order): RedirectResponse
    {
        return (new OrderReturnService($order))->redirect();
    }

    public function returnProduct(Order $order, OrderProduct $orderProduct): RedirectResponse
    {
        return (new OrderReturnProductService($orderProduct))->redirect();
    }

    public function ajaxGetProducts(Order $order): string
    {
        $output = '';

        foreach ($order->products as $orderProduct) {
            $output .=
                '<tr>' .
                    '<td>' . $orderProduct->product->name . '</td>' .
                    '<td>' . $orderProduct->category->name . '</td>' .
                    '<td>' . $orderProduct->price . '</td>' .
                    '<td>' . $orderProduct->quantity . '</td>' .
                '</tr>';
        }

        return $output;
    }
}
