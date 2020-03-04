<?php

use App\Transactions;
?>
@extends('layouts.default')
@section('content')
<h2>Order List</h2>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        @if (\Session::has('success'))
        <div class="alert alert-success">
            <p>{!! \Session::get('success') !!}</p>
        </div>
        @endif
    </div>
    <div class="col-md-3"></div>
</div>
<table id="order_list">
    <thead>
        <th>Time</th>
        <th>Purchaser</th>
        <th>Cashier</th>
        <th>Total Price</th>
        <th></th>
        <th>Status</th>
        <th></th>
    </thead>
    <tbody>
        @foreach (Transactions::orderBy('created_at', 'DESC')->get() as $transaction)
        <tr>
            <td class="table-text">
                <div>{{ $transaction->created_at }}</div>
            </td>
            <td class="table-text">
                <div>{{ DB::table('users')->where('id', $transaction->purchaser_id)->pluck('full_name')->first() }}</div>
            </td>
            <td class="table-text">
                <div>{{ DB::table('users')->where('id', $transaction->cashier_id)->pluck('full_name')->first() }}</div>
            </td>
            <td class="table-text">
                <div>${{ $transaction->total_price }}</div>
            </td>
            <td>
                <div><a href="orders/view/{{ $transaction->id }}">View</a></div>
            </td>
            <td class="table-text">
                <div>{{ $transaction->status == 0 ? "Normal" : "Returned" }}</div>
            </td>
            <td>
                @if($transaction->status == 0)
                <div><a href="orders/return/{{ $transaction->id }}">Return</a></div>
                @else
                <div>Returned</div>
                @endif
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
        "order": [
            [0, "desc"]
        ],
        paging: false,
        "scrollY": "250px",
        "scrollCollapse": true,
    });
</script>
@endsection