@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">Stock Ledger</h2>
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column is-two-thirds" id="product_container" style="visibility: hidden;">
        <table id="product_list" class="hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Stock Override</th>
                    <th>Box Size</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                <tr id="{{ $product->id }}">
                    <td>
                        <div>{{ $product->name }}</div>
                    </td>
                    <td>
                        <div>{{ $product->category->name }}</div>
                    </td>
                    <td>
                        <div>{!! $product->getStock() !!}</div>
                    </td>
                    <td>
                        <div>{{ $product->stock_override ? "✅" : "❌" }}</div>
                    </td>
                    <td>
                        <div>{!! $product->box_size === -1 ? '<i>N/A</i>' : $product->box_size !!}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="column">
        @include('includes.messages')
        <div id="adjust_product">
            @if(session()->has('last_product'))
                @include('pages.products.ledger.form', ['product' => session('last_product')])
            @endif
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        const productList = $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
        });
        $('#product_list').on('click', 'tbody tr', function() {
            let url = "{{ route('products_ledger_ajax', ":id") }}";
            url = url.replace(':id', productList.row(this).id());

            $.ajax({
                type : "GET",
                url : url,
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                beforeSend : function() {
                    $('#adjust_product').show().html("<center><img src='{{ url('img/loader.gif') }}' class='loading-spinner'></img></center>");
                },
                success : function(response) {
                    $('#adjust_product').html(response);
                },
                error: function(xhr, status, error) {
                    $('#adjust_product').show().html("<p style='color: red;'><b>ERROR: </b><br>" + xhr.responseText + "</p>");
                }
            });
        });
        $('tr').css('cursor','pointer');
        $('#loading').hide();
        $('#product_container').css('visibility', 'visible');
    });
</script>
@stop
