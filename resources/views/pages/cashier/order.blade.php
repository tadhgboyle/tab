@extends('layouts.default')
@section('content')
<h2>Cashier</h2>
<?php

use App\Products;
?>
<div class="row">
    <div class="col-md-2"></div>
    <div class="panel-body col-md-6">
        <form method="post" action="/cashier/order/{{ request()->route('id') }}/submit">
            {{ csrf_field() }}
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <table id="product_list">
                <thead>
                    <th></th>
                    <th>Name</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    @foreach(Products::all() as $product)
                    <tr>
                        <td class="table-text">
                            <center><input type="checkbox" id="product" name="product[]" value="{{ $product->id }}" /></center>
                        </td>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>${{ $product->price }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button>Submit</button>
        </form>
    </div>
    <div class="col-md-2">
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#product_list').DataTable();
    });
    $('#product_list').DataTable({
        paging: false
    });
</script>
@stop