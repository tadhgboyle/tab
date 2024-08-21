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
                <div class="column">
                    <a class="button is-light is-pulled-right is-small" href="{{ route('products_variants_create', $product) }}">
                        ➕ Create
                    </a>
                </div>
            </div>

            <table id="variants_list">
                <thead>
                    <th>SKU</th>
                    @foreach ($product->variantOptions as $option)
                        <th>{{ $option->name }}</th>
                    @endforeach
                    <th>Price</th>
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                        <th></th>
                    @endpermission
                </thead>
                <tbody>
                    @foreach($product->variants as $variant)
                    <tr>
                        <td>{{ $variant->sku }}</td>
                        @foreach ($product->variantOptions as $option)
                            <td>{!! $variant->optionValueAssignments->firstWhere('product_variant_option_id', $option->id)->productVariantOptionValue->value ?? '<i>Not set</i>' !!}</td>
                        @endforeach
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
                    <h4 class="title has-text-weight-bold is-4">Variant Options</h4>
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
                        <td>{{ $variantOption->values->map->value->implode(', ') }}</td>
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
            <p><strong>Price:</strong> {{ $product->price }}</p>
            <p><strong>PST:</strong> {{ $product->pst ? 'Yes' : 'No' }}</p>
        </div>

        <div class="box">
            <p><strong>Stock:</strong> {{ $product->getStock() }}</p>
            <p><strong>Unlimited stock:</strong> {{ $product->unlimited_stock ? 'Yes' : 'No' }}</p>
            <p><strong>Stock override:</strong> {{ $product->stock_override ? 'Yes' : 'No' }}</p>
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