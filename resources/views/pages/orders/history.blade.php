<?php

use App\Transactions;
?>
@extends('layouts.default')
@section('content')
<h2>User History</h2>
<p>User: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }}</p>
<span>Total spent: ${{ number_format(Transactions::where('purchaser_id', '=', request()->route('id'))->sum('total_price'), 2) }}</span>,&nbsp;
<span>Total returned: ${{ number_format(Transactions::where([['purchaser_id', '=', request()->route('id')], ['status', '=', '1']])->sum('total_price'), 2) }}</span>,&nbsp;
<span>Total owing: ${{ number_format(Transactions::where([['purchaser_id', '=', request()->route('id')], ['status', '=', '0']])->sum('total_price'), 2) }}</span>
<br>
<br>
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
        @foreach (Transactions::where('purchaser_id', '=', request()->route('id'))->get() as $transaction)
        <tr>
            <td class="table-text">
                <div>{{ $transaction->created_at }}</div>
            </td>
            <td class="table-text">
                <div> {{ DB::table('users')->where('id', $transaction->purchaser_id)->pluck('full_name')->first() }}</div>
            </td>
            <td class="table-text">
                <div> {{ DB::table('users')->where('id', $transaction->cashier_id)->pluck('full_name')->first() }}</div>
            </td>
            <td class="table-text">
                <div>${{ number_format($transaction->total_price, 2) }}</div>
            </td>
            <td class="table-text">
            <div>{!! $transaction->status == 0 ? "<h5><span class=\"badge badge-success\">Normal</span></h5>" : "<h5><span class=\"badge badge-danger\">Returned</span></h5>"!!}</div>
            </td>
            <td>
                <div><a href="/orders/view/{{ $transaction->id }}">View</a></div>
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
        ]
    });
</script>
@endsection