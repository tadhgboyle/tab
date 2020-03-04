<?php

use App\Products;
?>
@extends('layouts.default')
@section('content')
<h2>Product List</h2>
<table id="product_list">
    <thead>
        <th>Name</th>
        <th>Price</th>
        <th></th>
    </thead>
    <tbody>
        @foreach (Products::all() as $product)
        <tr>
            <td class="table-text">
                <div>{{ $product->name}}</div>
            </td>
            <td class="table-text">
                <div>${{ number_format($product->price, 2) }}</div>
            </td>
            <td>
                <div><a href="products/edit/{{ $product->id }}">Edit</a></div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable();
    });
    $('#product_list').DataTable({
        paging: false,
        "scrollY": "340px",
        "scrollCollapse": true,
    });
</script>
@endsection