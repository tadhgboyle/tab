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
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Variants</h4>
                </div>
                @if(!$product->variantOptions->isEmpty() && !$product->hasAllVariantCombinations())
                    <div class="column">
                        <a class="button is-light is-pulled-right is-small" href="{{ route('products_variants_create', $product) }}">
                            ➕ Create
                        </a>
                    </div>
                @endif
            </div>

            <table id="variants_list">
                <thead>
                    <th>SKU</th>
                    @foreach ($product->variantOptions as $option)
                        <th>{{ $option->name }}</th>
                    @endforeach
                    <th>Stock</th>
                    <th>Box size</th>
                    <th>Price</th>
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                        <th></th>
                    @endpermission
                </thead>
                <tbody>
                    @foreach($product->variants as $variant)
                    <tr>
                        <td><code>{{ $variant->sku }}</code></td>
                        @foreach ($product->variantOptions as $option)
                            <td>
                                @if ($optionValue = $variant->optionValueFor($option))
                                    <div class="tag">{{ $optionValue->value }}</div>
                                @else
                                    <i>Not set</i>
                                @endif
                            </td>
                        @endforeach
                        <td>{!! $variant->unlimited_stock ? '<i>Unlimited</i>' : $variant->stock !!}</td>
                        <td>{!! $variant->box_size ?? '<i>N/A</i>' !!}</td>
                        <td>{{ $variant->price }}</td>
                        @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                            <td><a href="{{ route('products_variants_edit', [$product->id, $variant->id]) }}">Edit</a></td>
                        @endpermission
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="box">
            <div class="columns">
                <div class="column">
                    <h4 class="title has-text-weight-bold is-4">Options</h4>
                </div>
                <div class="column">
                    <a class="button is-light is-pulled-right is-small" href="{{ route('products_variant-options_create', $product) }}">
                        ➕ Create
                    </a>
                </div>
            </div>

            <table id="variant_options_list">
                <thead>
                    <th>Name</th>
                    <th>Values</th>
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                        <th></th>
                    @endpermission
                </thead>
                <tbody>
                    @foreach($product->variantOptions as $variantOption)
                    <tr>
                        <td>{{ $variantOption->name }}</td>
                        <td>
                            @foreach($variantOption->values as $value)
                                <div class="tag">{{ $value->value }}</div>
                            @endforeach
                        </td>
                        @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                            <td><a href="{{ route('products_variant-options_edit', [$product->id, $variantOption->id]) }}">Edit</a></td>
                        @endpermission
                    </tr>
                    @endforeach
                </tbody>
            </table>
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

<script>
$('#variant_options_list').DataTable({
    "paging": false,
    "info": false,
    "searching": false,
    "ordering": false
});

$('#variants_list').DataTable({
    "paging": false,
    "info": false,
    "searching": false,
    "ordering": false
});
</script>

@endsection