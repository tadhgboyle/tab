@php

use App\Product;
use App\Helpers\UserLimitsHelper;
@endphp
@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">Stock Adjustment</h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column is-two-thirds" id="product_container" style="visibility: hidden;">
        <table id="product_list">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Stock Override</th>
                    <th>Box Size</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach(Product::where('deleted', false)->get() as $product)
                <tr>
                    <td>
                        <div>{{ $product->name }}</div>
                    </td>
                    <td>
                        <div>{{ ucfirst($product->category) }}</div>
                    </td>
                    <td>
                        <div>{!! $product->getStock() !!}</div>
                    </td>
                    <td>
                        <div>{{ $product->stock_override ? 'True' : 'False' }}</div>
                    </td>
                    <td>
                        <div>{!! $product->box_size == -1 ? '<i>N/A</i>' : $product->box_size !!}</div>
                    </td>
                    <td>
                        <div class="control">
                        <!-- TODO: Should we disable button if it has unlimited stock? Makes it harder to edit on the fly... -->
                            <button class="button is-info" id="adjust_select" value="{{ $product->id }}">
                                <span class="icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                            </button>
                        </div>
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
                @include('pages.products.adjust.form', ['product' => session('last_product')])
            @endif
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#product_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [
                { 
                    "orderable": false, 
                    "searchable": false,
                    "targets": 5
                }
            ]
        });
        $('#loading').hide();
        $('#product_container').css('visibility', 'visible');
    });

   $(document).on("click", "#adjust_select", function() {
        $.ajax({
            type : "POST",
            url : "{{ route('products_adjust_ajax') }}",
            data: {
                "_token": "{{ csrf_token() }}",
                "id": $(this).attr("value")
            },
            beforeSend : function() {
                $('#adjust_product').show().html("<center><img src='{{ url('loader.gif') }}' class='loading-spinner'></img></center>");
            },
            success : function(response) {
                $('#adjust_product').html(response);
            },
            error: function(xhr, status, error) {
                $('#adjust_product').show().html("<p style='color: red;'><b>ERROR: </b><br>" + xhr.responseText + "</p>");
            }
        });
    });
</script>
@stop