@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">View Product</h2>
<h4 class="subtitle">
    {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_edit', $product->id) }}">(Edit)</a>@endpermission
</h4>

<div class="columns">
    <div class="column is-two-thirds">
        <div class="mb-5">
            <livewire:admin.products.variants-list :product="$product" />
        </div>

        <livewire:admin.products.variant-options-list :product="$product" />
    </div>
    <div class="column">
        <x-detail-card-stack>
            <x-detail-card title="Details">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Status" :value="$product->status->getWord()" />
                    <x-detail-card-item label="Category" :value="$product->category->name" />
                    @if(!$product->hasVariants() && $product->sku)
                        <x-detail-card-item label="SKU" :value="$product->sku" />
                    @endif
                </x-detail-card-item-list>
            </x-detail-card>

            <x-detail-card title="Pricing">
                <x-detail-card-item-list>
                    <x-detail-card-item label="Price" :value="$product->hasVariants() ? $product->getVariantPriceRange() : $product->price" />
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

            <x-detail-card title="Recent Orders">
                <x-detail-card-item-list>
                        @foreach($product->recentOrders() as $order)
                            <x-detail-card-item label="<a href='{{ route('orders_view', $order->id) }}'>{{ $order->identifier }}</a>" :value="$order->created_at->diffForHumans()" />
                        @endforeach
                </x-detail-card-item-list>
            </x-detail-card>
        </x-detail-card-stack>
    </div>
</div>
@endsection