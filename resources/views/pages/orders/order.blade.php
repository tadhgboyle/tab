@extends('layouts.default')
@section('content')
<?php

use App\Products;
use App\User;
?>
<h2>Cashier</h2>
<p>Purchaser: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }}</p>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-6">
        @if (\Session::has('error'))
        <div class="alert alert-danger">
            <p>{!! \Session::get('error') !!}</p>
        </div>
        @endif
        <form method="post" id="order" action="/orders/{{ request()->route('id') }}/submit">
            @csrf
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <input type="hidden" id="purchaser_balance" value="{{ User::where('id', '=', request()->route('id'))->pluck('balance')->first() }}">
            <table id="product_list">
                <thead>
                    <th></th>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    @foreach(Products::all() as $product)
                    <tr>
                        <td class="table-text">
                            <center><input type="checkbox" name="product[]" value="{{ $product->id }}" id="{{ $product->name . ' $' . $product->price }}" class="clickable" /></center>
                        </td>
                        <td class="table-text">
                            <center><input type="number" name="quantity[{{ $product->id }}]" value="1" /></center>
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
        </form>
    </div>
    <div class="col-md-4" align="center">
        <h3>Items</h3>
        <div id="items"></div>
        <hr>
        <div id="total_price"></div>
        <div id="remaining_balance"></div>
        <input type="submit" form="order" value="Submit" class="disableable">
    </div>
</div>
<script>
    $(document).ready(function() {
        var table = $('#product_list').DataTable({
            paging: false,
            "scrollY": "250px",
            "scrollCollapse": true,
        });
        // handle the item sidebar
        const checked = [];
        var total_price = 0.00;
        const purchaser_balance = parseFloat(document.getElementById('purchaser_balance').value).toFixed(2);
        $("#total_price").html('Total Price: $' + total_price.toFixed(2));
        $("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));

        $('.clickable').click(function() {
            if ($(this).is(':checked')) {
                checked.push($(this).attr('id') + '<br>');
                total_price += parseFloat($(this).attr('id').split('$')[1]);
            } else {
                const index = checked.indexOf($(this).attr('id') + '<br>');
                if (index >= 0) {
                    checked.splice(index, 1);
                    total_price -= parseFloat($(this).attr('id').split('$')[1]);
                }
            }
            $("#items").html(checked);
            $("#total_price").html('Total Price: $' + total_price.toFixed(2));
            $("#remaining_balance").html('Remaining Balance: $' + purchaser_balance);
            if (total_price > purchaser_balance) {
                $('.disableable').prop('disabled', true);
                $("#total_price").html('<span style="color:red">Total Price: $' + total_price.toFixed(2) + '</span>');
                $("#remaining_balance").html('<span style="color:red">Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2) + '</span>');
            } else {
                $('.disableable').prop('disabled', false);
                $("#total_price").html('Total Price: $' + total_price.toFixed(2));
                $("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));
            }
        });
    });
</script>
@stop