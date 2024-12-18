@extends('layouts.default', ['page' => 'products'])
@section('content')
<x-page-header :title="$product->name" :actions="[
    [
        'label' => 'Edit',
        'href' => route('products_edit', $product->id),
        'can' => hasPermission(\App\Helpers\Permission::PRODUCTS_MANAGE)
    ],
]" />

<div class="grid grid-cols-6 gap-5">
    <div class="col-span-4">
        <x-detail-card-stack>
            <livewire:admin.products.variants-list :product="$product" />
            <livewire:admin.products.variant-options-list :product="$product" />
        </x-detail-card-stack>
    </div>
    <div class="col-span-2">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Status">
                        <x-product-status-badge :product="$product" />
                    </x-detail-card-item>
                    <x-detail-card-item label="Category">
                        <x-badge :value="$product->category->name" />
                    </x-detail-card-item>
                    @if(!$product->hasVariants() && $product->sku)
                        <x-detail-card-item label="SKU" :value="$product->sku" />
                    @endif
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Pricing">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Price" :value="$product->hasVariants() ? $product->getVariantPriceRange() : $product->price" />
                    @if(hasPermission(\App\Helpers\Permission::PRODUCTS_VIEW_COST) && ($product->cost?->isPositive() || $product->getVariantCostRange()))
                    <x-detail-card-item label="Cost">
                        {{ $product->hasVariants() ? $product->getVariantCostRange() : $product->cost }} @if(!$product->hasVariants() && $product->cost?->isPositive()) ({{ $product->profitMargin() }}%) @endif
                    </x-detail-card-item>
                    @endif
                    <x-detail-card-item label="PST" :value="$product->pst ? '✅' : '❌'" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Inventory">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Stock" :value="$product->getStock()" />
                    @unless($product->unlimited_stock)
                        <x-detail-card-item label="Stock override" :value="$product->stock_override ? '✅' : '❌'" />
                    @endunless
                    <x-detail-card-item label="Restore stock on return" :value="$product->restore_stock_on_return ? '✅' : '❌'" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Insights" subtitle="Last 30 days">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Total Orders" :value="$product->totalRecentOrders()" />
                    <x-detail-card-item label="Total Units" :value="$product->totalRecentUnits()" />
                    <x-detail-card-item label="Total Revenue" :value="$product->totalRecentRevenue()" />
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Recent Orders">
                <x-detail-card-item-list>
                        @forelse($product->recentOrders() as $order)
                            <x-detail-card-item label="<a href='{{ route('orders_view', $order->id) }}'>{{ $order->identifier }}</a>" :value="$order->created_at->diffForHumans()" />
                        @empty
                            <p class="py-2 text-sm text-gray-500">No recent orders</p>
                        @endforelse
                </x-detail-card-item-list>
            </x-detail-card>
        </x-detail-card-stack>
    </div>
</div>
@endsection