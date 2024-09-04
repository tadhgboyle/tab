@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">View Product</h2>
<h4 class="subtitle">
    {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)<a href="{{ route('products_edit', $product->id) }}">(Edit)</a>@endpermission
</h4>

@include('includes.messages')

<div class="columns">
    <div class="column is-two-thirds">
        <div class="box">
            <livewire:products.variants-list :product="$product" />
        </div>

        <div class="box">
            <livewire:products.variant-options-list :product="$product" />
        </div>
    </div>
    <div class="column">
        <div class="box">
            <p><strong>Category:</strong> {{ $product->category->name }}</p>
            @if(!$product->hasVariants() && $product->sku)
                <p><strong>SKU:</strong> {{ $product->sku }}</p>
            @endif
        </div>

        <div class="box">
            <p>
                <strong>Price:</strong>
                @if($product->hasVariants())
                    {{ $product->getVariantPriceRange() }}
                @else
                    {{ $product->price }}
                @endif
            </p>
            <p><strong>PST:</strong> {{ $product->pst ? '✅' : '❌' }}</p>
        </div>

        <div class="box">
            <p><strong>Stock:</strong> {!! $product->getStock() !!} @if(!$product->unlimited_stock && $product->hasVariants()) (across {{ $product->variants->count() }} variants)</p> @endif
            @unless($product->unlimited_stock)
                <p><strong>Stock override:</strong> {{ $product->stock_override ? '✅' : '❌' }}</p>
            @endunless
            <p><strong>Restore stock on return:</strong> {{ $product->restore_stock_on_return ? '✅' : '❌' }}</p>
        </div>

        <div class="box">
            <p><strong>Recent orders</strong></p>
            <ul>
                @foreach($product->recentOrders() as $order)
                    <li>
                        <a href="{{ route('orders_view', $order->id) }}">#{{ $order->id }}</a> - {{ $order->created_at->diffForHumans() }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection