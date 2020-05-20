<?php

use App\Http\Controllers\OrderController;
use App\Products;
use App\Transactions;

$transaction = Transactions::where('id', '=', request()->route('id'))->get();
$transaction_items = explode(", ", $transaction['0']['products']);
?>
@extends('layouts.default')
@section('content')
<h2>View Order</h2>
<div class="row">
    <div class="col-md-6">
        @include('includes.messages')
        <br>
        <h4>Order ID: {{request()->route('id') }}</h4>
        <h4>Time: {{ $transaction['0']['created_at']->format('M jS Y h:ia') }}</h4>
        <h4>Purchaser: <a
                href="/users/info/{{ $transaction['0']['purchaser_id'] }}">{{ DB::table('users')->where('id', $transaction['0']['purchaser_id'])->pluck('full_name')->first() }}</a>
        </h4>
        <h4>Cashier: <a
                href="/users/info/{{ $transaction['0']['cashier_id'] }}">{{ DB::table('users')->where('id', $transaction['0']['cashier_id'])->pluck('full_name')->first() }}</a>
        </h4>
        <h4>Total Price: ${{ number_format($transaction['0']['total_price'], 2) }}</h4>
        <h4>Status: {{ OrderController::checkReturned($transaction['0']['id']) ? "" : "Not" }} Returned</h4>
        <br>
        @if(!OrderController::checkReturned($transaction['0']['id']))
        <form>
            <input type="hidden" id="transaction_id" value="{{ $transaction['0']['id'] }}">
            <a href="javascript:;" data-toggle="modal" data-target="#returnModal"
                class="btn btn-xs btn-danger">Return</a>
        </form>
        @endif
    </div>
    <div class="col-md-6">
        <h2 align="center">Items</h2>
        <table id="product_list">
            <thead>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Item Price</th>
                <th></th>
            </thead>
            <tbody>
                @foreach($transaction_items as $product)
                <?php
                $item_info = OrderController::deserializeProduct($product);
                ?>
                <tr>
                    <td class="table-text">
                        <div>{{ $item_info['name'] }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ number_format($item_info['price'], 2) }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ $item_info['quantity'] }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ number_format($item_info['price'] * $item_info['quantity'], 2) }}</div>
                    </td>
                    <td class="table-text">
                        <div>
                            @if($transaction['0']['status'] == 0 && $item_info['returned'] < $item_info['quantity'])
                                <form>
                                <input type="hidden" id="item_id" value="{{ $item_info['id'] }}">
                                <a href="javascript:;" data-toggle="modal"
                                    onclick="window.location='/orders/return/item/{{ $item_info['id'] }}/{{ $transaction['0']['id'] }}';"
                                    class="btn btn-xs btn-danger">Return
                                    ({{ $item_info['quantity'] - $item_info['returned'] }})</a>
                                </form>
                                @else
                                Returned
                                @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div id="returnModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <form action="" id="returnForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <p class="text-center">Are you sure you want to return this transaction?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal"
                            onclick="returnData()">Return</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        var table = $('#product_list').DataTable({
            paging: false,
            "scrollY": "300px",
            "scrollCollapse": true,
        });
    });

    function returnData() {
        let url = '{{ route("return_order", ":id") }}';
            url = url.replace(':id', document.getElementById('transaction_id').value);
        $("#returnForm").attr('action', url);
        $("#returnForm").submit();
    }
</script>
@endsection