@extends('layouts.default', ['page' => 'purchase_orders'])
@section('content')
<x-page-header :title="$purchaseOrder->reference" :actions="[
    [
        'label' => 'Submit',
        'color' => 'primary',
        'onclick' => 'openSubmitModal',
        'can' => hasPermission(\App\Helpers\Permission::PURCHASE_ORDERS_MANAGE) && $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Draft
    ],
    [
        'label' => 'Edit',
        'color' => 'primary',
        'href' => route('purchase_orders_edit', $purchaseOrder->id),
        'can' => hasPermission(\App\Helpers\Permission::PURCHASE_ORDERS_MANAGE) && $purchaseOrder->status !== \App\Enums\PurchaseOrderStatus::Completed
    ],
    [
        'label' => 'Cancel',
        'color' => 'danger',
        'onclick' => 'openCancelModal',
        'can' => hasPermission(\App\Helpers\Permission::PURCHASE_ORDERS_MANAGE) && $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Pending || $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Draft
    ]
]" />

<div class="grid grid-cols-6 gap-5">
    <div class="col-span-4">
        <livewire:admin.purchase-order-products-list :purchaseOrder="$purchaseOrder" />
    </div>
    <div class="col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Status">
                        <x-purchase-order-status-badge :purchaseOrder="$purchaseOrder" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Expected delivery date">
                        {{ $purchaseOrder->expected_delivery_date->format('M jS Y') }}
                    </x-detail-card-item>
                    @if($purchaseOrder->delivery_date)
                        <x-detail-card-item label="Delivery date">
                            {{ $purchaseOrder->delivery_date->format('M jS Y') }}
                        </x-detail-card-item>
                    @endif
                    <x-detail-card-item label="Products">
                        {{ $purchaseOrder->totalQuantity() }} units across {{ $purchaseOrder->products->count() }} products
                    </x-detail-card-item>
                    <x-detail-card-item label="Cost">
                        {{ $purchaseOrder->totalCost() }}
                    </x-detail-card-item>
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Supplier">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Name">
                        <x-badge :value="$purchaseOrder->supplier->name" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Address" :value="$purchaseOrder->supplier->address" />
                    <x-detail-card-item label="Contact" :value="$purchaseOrder->supplier->contact_name" />
                    <x-detail-card-item label="Email" :value="$purchaseOrder->supplier->contact_email" />
                    <x-detail-card-item label="Phone" :value="$purchaseOrder->supplier->contact_phone" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Status">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Received">
                        {{ $purchaseOrder->receivedCost() }} across {{ $purchaseOrder->receivedQuantity() }} units
                    </x-detail-card-item>
                    <x-detail-card-item label="Outstanding">
                        {{ $purchaseOrder->outstandingCost() }} across {{ $purchaseOrder->outstandingQuantity() }} units
                    </x-detail-card-item>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $purchaseOrder->products->sum('received_quantity') / $purchaseOrder->products->sum('quantity') * 100 }}%"></div>
                    </div>
                </x-detail-card-item-list>
            </x-detail-card>
        </x-detail-card-stack>
    </div>
</div>
@endsection