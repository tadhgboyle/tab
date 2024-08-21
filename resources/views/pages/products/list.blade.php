@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">Product List</h2>
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column" id="product_container" style="visibility: hidden;">
        @include('includes.messages')
        <table id="product_list">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    @permission(\App\Helpers\Permission::PRODUCTS_VIEW)
                    <th></th>
                    @endpermission
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                    <th></th>
                    @endpermission
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td>
                        <div>{{ $product->name }}</div>
                    </td>
                    <td>
                        <div>{{ $product->category->name }}</div>
                    </td>
                    <td>
                        <div>{!! $product->price->isZero() ? '<i>Free</i>' :  $product->price !!}</div>
                    </td>
                    <td>
                        <div>{!! $product->getStock() !!}</div>
                    </td>
                    @permission(\App\Helpers\Permission::PRODUCTS_VIEW)
                    <td>
                        <div><a href="{{ route('products_view', $product->id) }}">View</a></div>
                    </td>
                    @endpermission
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                    <td>
                        <div><a href="{{ route('products_edit', $product->id) }}">Edit</a></div>
                    </td>
                    @endpermission
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    @permission(\App\Helpers\Permission::PRODUCTS_VIEW)
                    4,
                    @endpermission
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                    5,
                    @endpermission
                ]
            }]
        });
        $('#loading').hide();
        $('#product_container').css('visibility', 'visible');
    });
</script>
@endsection
