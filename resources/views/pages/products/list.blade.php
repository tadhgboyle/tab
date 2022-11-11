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
                    <th>Stock Override</th>
                    <th>Box Size</th>
                    <th>PST</th>
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
                    <td>
                        <div><span class="tag is-medium">{{ $product->stock_override ? "Yes" : "No" }}</span></div>
                    </td>

                    <td>
                        <div>{!! $product->box_size === -1 ? '<i>N/A</i>' : $product->box_size !!}</div>
                    </td>
                    <td>
                        <div><span class="tag is-medium">{{ $product->pst ? "Yes" : "No" }}</div>
                    </td>
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
                    4,
                    6,
                    @permission(\App\Helpers\Permission::PRODUCTS_MANAGE)
                    7
                    @endpermission
                ]
            }]
        });
        $('#loading').hide();
        $('#product_container').css('visibility', 'visible');
    });
</script>
@endsection
