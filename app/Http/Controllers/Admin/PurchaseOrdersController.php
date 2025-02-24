<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;

class PurchaseOrdersController extends Controller
{
    public function index()
    {
        return view('pages.admin.products.purchase-orders.list');
    }

    public function create()
    {
        return view('pages.admin.products.purchase-orders.form');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return view('pages.admin.products.purchase-orders.view', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return view('pages.admin.products.purchase-orders.form', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Cancelled,
        ]);

        return redirect()->route('purchase_orders_list')->with('success', 'Purchase order cancelled.');
    }
}
