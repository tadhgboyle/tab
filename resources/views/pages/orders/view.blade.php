<?php

use App\Products;
use App\Transactions;

$transaction = Transactions::where('id', '=', request()->route('id'))->get();
$transaction_items = explode(", ", $transaction['0']['products']);
?>
@extends('layouts.default')
@section('content')
<h2>View Order</h2>
<div class="row">
    <div class="col-md-8">
        <br>
        <h4>Order ID: {{request()->route('id') }}</h4>
        <h4>Time: {{ $transaction['0']['created_at'] }}</h4>
        <h4>Purchaser: {{ DB::table('users')->where('id', $transaction['0']['purchaser_id'])->pluck('full_name')->first() }}</h4>
        <h4>Cashier: {{ DB::table('users')->where('id', $transaction['0']['cashier_id'])->pluck('full_name')->first() }}</h4>
        <h4>Total Price: ${{ $transaction['0']['total_price'] }}</h4>
        <h4>Status: {{ $transaction['0']['status'] == 0 ? "Normal" : "Returned" }}</h4>
        @if($transaction['0']['status'] == 0)
        <h4><a href="orders/return/{{ $transaction['0']['id'] }}">Return</a></h4>
        @endif
    </div>
    <div class="col-md-4">
        <h2 align="center">Items</h2>
        <table id="product_list">
            <thead>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Item Price</th>
            </thead>
            <tbody>
                @foreach($transaction_items as $product)
                <?php
                $item_info = Products::select('name', 'price')->where('id', '=', strtok($product, "*"))->get();
                $quantity = substr($product, strpos($product, "*") + 1);
                ?>
                <tr>
                    <td class="table-text">
                        <div>{{ $item_info['0']['name'] }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ $item_info['0']['price'] }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ $quantity }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ $item_info['0']['price'] * $quantity }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        var table = $('#product_list').DataTable({
            paging: false,
            // we want the scroll to be as big as possible without making the page scroll
            "scrollY": "300px",
            "scrollCollapse": true,
        });
    });
</script>
@endsection