@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">View Product</h2>
<h4 class="subtitle">
    {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_edit', $product->id) }}">(Edit)</a>@endpermission
</h4>

<div class="columns">
    <div class="column is-two-thirds">
        <div class="mb-5">
            <livewire:products.variants-list :product="$product" />
        </div>

        <livewire:products.variant-options-list :product="$product" />
    </div>
    <div class="column">
        <x-detail-card title="Details">
            <p><strong>Status:</strong> {{ $product->status->getWord() }}</p>
            <p><strong>Category:</strong> {{ $product->category->name }}</p>
            @if(!$product->hasVariants() && $product->sku)
                <p><strong>SKU:</strong> {{ $product->sku }}</p>
            @endif
        </x-detail-card>

        <x-detail-card title="Pricing">
            <p>
                <strong>Price:</strong>
                @if($product->hasVariants())
                    {{ $product->getVariantPriceRange() }}
                @else
                    {{ $product->price }}
                @endif
            </p>
            <p><strong>PST:</strong> {{ $product->pst ? '✅' : '❌' }}</p>
        </x-detail-card>

        <x-detail-card title="Inventory">
            <p><strong>Stock:</strong> {!! $product->getStock() !!} @if(!$product->unlimited_stock && $product->hasVariants()) (across {{ $product->variants->count() }} variants)</p> @endif
            @unless($product->unlimited_stock)
                <p><strong>Stock override:</strong> {{ $product->stock_override ? '✅' : '❌' }}</p>
            @endunless
            <p><strong>Restore stock on return:</strong> {{ $product->restore_stock_on_return ? '✅' : '❌' }}</p>
        </x-detail-card>

        <x-detail-card title="Recent Orders">
            <ul>
                @foreach($product->recentOrders() as $order)
                    <li>
                        <a href="{{ route('orders_view', $order->id) }}">{{ $order->identifier }}</a> - {{ $order->created_at->diffForHumans() }}
                    </li>
                @endforeach
            </ul>
        </x-detail-card>
    </div>
</div>
@endsection