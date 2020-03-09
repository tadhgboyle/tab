@extends('layouts.default')
@section('content')
<?php

use App\Products;
use App\User;
use App\Http\Controllers\SettingsController;
?>
<h2>Cashier</h2>
<p>User: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }}</p>
<div class="row">
    <div class="col-md-1"></div>
    <div class="col-md-7">
        @include('includes.messages')
        <form method="post" id="order" action="/orders/{{ request()->route('id') }}/submit">
            @csrf
            <input type="hidden" name="purchaser_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="cashier_id" value="{{ Auth::user()->id }}">
            <input type="hidden" id="current_gst" value="{{ SettingsController::getGst() }}">
            <input type="hidden" id="current_pst" value="{{ SettingsController::getPst() }}">

            <input type="hidden" id="purchaser_balance" value="{{ User::where('id', '=', request()->route('id'))->pluck('balance')->first() }}">
            <table id="product_list">
                <thead>
                    <th></th>
                    <th>Quantity</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                </thead>
                <tbody>
                    @foreach(Products::all() as $product)
                    <tr>
                        <td class="table-text">
                            <center><input type="checkbox" name="product[]" value="{{ $product->id }}" id="{{ $product->name . ' $' . $product->price }}" class="clickable" /></center>
                            <input type="hidden" id="pst[{{ $product->id }}]" name="pst[{{ $product->id }}]" value="{{ $product->pst }}" />
                        </td>
                        <td class="table-text">
                            <center><input type="number" name="quantity[{{ $product->id }}]" id="quantity[{{ $product->id }}]" value="1" /></center>
                        </td>
                        <td class="table-text">
                            <div>{{ $product->name }}</div>
                        </td>
                        <td class="table-text">
                            <div>{{ ucfirst($product->category) }}</div>
                        </td>
                        <td class="table-text">
                            <div>${{ number_format($product->price, 2) }}</div>
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
        <div id="gst"></div>
        <div id="pst"></div>
        <div id="total_price"></div>
        <div id="remaining_balance"></div>
        <input type="submit" form="order" value="Submit" class="disableable">
        <span>&nbsp;&nbsp;</span>
        <input type="submit" onclick="window.location='/';" value="Cancel">
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
        // handle the item sidebar
        // refractor this!!
        const checked = [];
        var total_gst = 0.00;
        var total_pst = 0.00;
        const current_gst = parseFloat(document.getElementById('current_gst').value).toFixed(2);
        const current_pst = parseFloat(document.getElementById('current_pst').value).toFixed(2);
        var total_tax_percent = 0.00;
        var total_price = 0.00;
        const purchaser_balance = parseFloat(document.getElementById('purchaser_balance').value).toFixed(2);
        var quantity = 1;
        $("#gst").html('GST: $' + total_gst.toFixed(2));
        $("#pst").html('PST: $' + total_pst.toFixed(2));
        $("#total_price").html('Total Price: $' + total_price.toFixed(2));
        $("#remaining_balance").html('Remaining Balance: $' + (purchaser_balance - total_price).toFixed(2));
        $('.clickable').click(function() {
            const quantity_id = document.getElementById('quantity[' + document.getElementById($(this).attr('id')).value + ']');
            const pst_id = document.getElementById('pst[' + document.getElementById($(this).attr('id')).value + ']').value;
            if (quantity_id == 0) {
                return;
            }
            if ($(this).is(':checked')) {
                if (pst_id == 1) {
                    total_tax_percent += ((parseFloat(current_pst) + parseFloat(current_gst)) - 1).toFixed(2);
                    total_pst += parseFloat(((parseFloat($(this).attr('id').split('$')[1] * quantity_id.value)) * current_pst) - parseFloat($(this).attr('id').split('$')[1] * quantity_id.value));
                } else {
                    total_tax_percent += parseFloat(current_gst).toFixed(2);
                }
                quantity_id.disabled = true;
                quantity = parseInt(quantity_id.value);
                checked.push($(this).attr('id') + ' (x' + quantity + ')<br>');
                total_gst += parseFloat(((parseFloat($(this).attr('id').split('$')[1] * quantity)) * current_gst) - parseFloat($(this).attr('id').split('$')[1] * quantity));
                total_price += (parseFloat($(this).attr('id').split('$')[1]) * quantity) * total_tax_percent;
            } else {
                if (pst_id == 1) {
                    total_tax_percent += ((parseFloat(current_pst) + parseFloat(current_gst)) - 1).toFixed(2);
                    total_pst -= parseFloat(((parseFloat($(this).attr('id').split('$')[1] * quantity_id.value)) * current_pst) - parseFloat($(this).attr('id').split('$')[1] * quantity_id.value));
                } else {
                    total_tax_percent += parseFloat(current_gst).toFixed(2);
                }
                quantity_id.disabled = false;
                quantity = parseInt(quantity_id.value);
                const index = checked.indexOf($(this).attr('id') + ' (x' + quantity + ')<br>');
                if (index >= 0) {
                    checked.splice(index, 1);
                    total_gst -= parseFloat(((parseFloat($(this).attr('id').split('$')[1] * quantity)) * current_gst) - parseFloat($(this).attr('id').split('$')[1] * quantity));
                    total_price -= (parseFloat($(this).attr('id').split('$')[1]) * quantity) * total_tax_percent;
                }
            }
            $("#items").html(checked);
            $("#gst").html('GST: $' + total_gst.toFixed(2));
            $("#pst").html('PST: $' + total_pst.toFixed(2))
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
            total_tax_percent = 0.00;
        });
        // if we dont have this things will break
        $('form').submit(function(e) {
            $(':disabled').each(function(e) {
                $(this).removeAttr('disabled');
            })
        });
    });
</script>
@stop