@php

use App\Products;
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">Product List</h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
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
                    <th>Box Size</th>
                    <th>PST</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach (Products::all()->where('deleted', false) as $product)
                    <tr>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>{{ ucfirst($product->category) }}</div>
                        </td>
                        <td class="table-text">
                            <div>${{ number_format($product->price, 2) }}</div>
                        </td>
                        <td class="table-text">
                            <div>{!! Products::getStock($product->id) !!}</div>
                        </td>
                        <td class="table-text">
                            <div>{!! $product->box_size == -1 ? '<i>N/A</i>' : $product->box_size !!}</div>
                        </td>
                        <td>
                            <div>{!! $product->pst ? "<span class=\"tag is-success is-medium\">Yes</span>" : "<span class=\"tag is-danger is-medium\">No</span>" !!}</div>
                        </td>
                        <td>
                            <div><a href="products/edit/{{ $product->id }}">Edit</a></div>
                        </td>
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
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": [5, 6]
                }
            ]
        });
        $('#loading').hide();
        $('#product_container').css('visibility', 'visible');
    });
</script>
@endsection