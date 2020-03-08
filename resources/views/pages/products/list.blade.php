<?php

use App\Products;
?>
@extends('layouts.default')
@section('content')
<h2>Product List</h2>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        @if (\Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <span>{!! \Session::get('success') !!}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
    </div>
    <div class="col-md-3"></div>
</div>
<table id="product_list">
    <thead>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>PST</th>
        <th></th>
    </thead>
    <tbody>
        @foreach (Products::all() as $product)
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
            <td>
                <div>{!! $product->pst == 0 ? "<h5><span class=\"badge badge-danger\">No</span></h5>" : "<h5><span class=\"badge badge-success\">Yes</span></h5>"!!}</div>
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