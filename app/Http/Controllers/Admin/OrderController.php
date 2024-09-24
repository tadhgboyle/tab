<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Helpers\Permission;
use App\Enums\ProductStatus;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Orders\OrderCreateService;
use App\Services\Orders\OrderReturnService;
use App\Services\Orders\OrderReturnProductService;

class OrderController extends Controller
{
    public function index()
    {
        return view('pages.admin.orders.list');
    }

    public function show(Order $order)
    {
        return view('pages.admin.orders.view', [
            'order' => $order,
        ]);
    }

    public function create(User $user, SettingsHelper $settingsHelper)
    {
        if (!hasPermission(Permission::CASHIER_SELF_PURCHASES) && $user->id === auth()->id()) {
            return redirect()->route('cashier')->with('error', 'You cannot make purchases for yourself.');
        }

        return view('pages.admin.orders.order', [
            'user' => $user,
            'products' => Product::where('status', ProductStatus::Active)->with(
                'category',
                'variantOptions',
                'variants',
                'variants.product',
                'variants.optionValueAssignments',
                'variants.optionValueAssignments.productVariantOption',
                'variants.optionValueAssignments.productVariantOptionValue',
            )->orderBy('name')->get(),
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
}
