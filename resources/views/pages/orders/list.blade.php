@php

use App\Transactions;
use App\Http\Controllers\OrderController;
use App\User;
@endphp
@extends('layouts.default', ['page' => 'orders'])
@section('content')
<h2 class="title has-text-weight-bold">Order List</h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column" id="order_container" style="visibility: hidden;">
        @include('includes.messages')
        <table id="order_list">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Purchaser</th>
                    <th>Cashier</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach (Transactions::orderBy('created_at', 'DESC')->get() as $transaction)
                    @php $user = User::find($transaction->purchaser_id) @endphp
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
                                "<span class=\"tag is-success is-medium\">Normal</span>" :
                                "<span class=\"tag is-danger is-medium\">Returned</span>"!!}
                            </div>
                        </td>
                        <td>
                            <div><a href="orders/view/{{ $transaction->id }}">View</a></div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#order_list').DataTable({
        "paging": false,
        "scrollY": "27vw",
        "scrollCollapse": true,
        "order": [],
        "columnDefs": [
            { 
                "orderable": false, 
                "targets": [4, 5]
            }
        ]
    });
    $('#loading').hide();
    $('#order_container').css('visibility', 'visible');
});
</script>
@endsection