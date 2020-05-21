<?php

use App\Transactions;
use App\Http\Controllers\OrderController;
use App\User;
?>
@extends('layouts.default')
@section('content')
<h2>Order List</h2>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        @include('includes.messages')
    </div>
    <div class="col-md-3"></div>
</div>
<table id="order_list">
    <thead>
        <th>Time</th>
        <th>Purchaser</th>
        <th>Cashier</th>
        <th>Total Price</th>
        <th>Status</th>
        <th></th>
    </thead>
    <tbody>
        @foreach (Transactions::orderBy('created_at', 'DESC')->get() as $transaction)
        @php $user = User::find($transaction->purchaser_id) @endphp
        @if($user == null) @continue @endif
        <tr>
            <td class="table-text">
                <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
            </td>
            <td class="table-text">
                <div>
                    <a href="users/info/{{ $user->id }}">{{ $user->full_name }}</a>
                </div>
            </td>
            <td class="table-text">
                <div>{{ User::find($transaction->cashier_id)->full_name }}</div>
            </td>
            <td class="table-text">
                <div>${{ number_format($transaction->total_price, 2) }}</div>
            </td>
            <td class="table-text">
                <div>{!! !OrderController::checkReturned($transaction->id) ?
                    "<h5><span class=\"badge badge-success\">Normal</span></h5>" :
                    "<h5><span class=\"badge badge-danger\">Returned</span></h5>"!!}
                </div>
            </td>
            <td>
                <div><a href="orders/view/{{ $transaction->id }}">View</a></div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#order_list').DataTable();
    });
    $('#order_list').DataTable({
        "order": [],
        "paging": false,
        "scrollY": "26vw",
        "scrollCollapse": true,
    });
</script>
@endsection