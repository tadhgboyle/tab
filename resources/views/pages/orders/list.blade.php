@php

use App\Transactions;
use App\Http\Controllers\OrderController;
use App\User;
use App\Roles;
$orders_view = Auth::user()->hasPermission('orders_view');
$users_view = Auth::user()->hasPermission('users_view');
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
                    @if ($orders_view)
                    <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach (Transactions::orderBy('created_at', 'DESC')->get() as $transaction)
                @php $user = $transaction->purchaser_id @endphp
                <tr>
                    <td>
                        <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td>
                        <div>
                            @if ($users_view)
                            <a href="{{ route('users_view', $user->id) }}">{{ $user->full_name }}</a>
                            @else
                            {{ $user->full_name }}
                            @endif
                        </div>
                    </td>
                    <td>
                        <div>{{ $transaction->cashier_id->full_name }}</div>
                    </td>
                    <td>
                        <div>${{ number_format($transaction->total_price, 2) }}</div>
                    </td>
                    <td>
                        <div>
                            @switch(OrderController::checkReturned($transaction))
                                @case(0)
                                    <span class="tag is-success is-medium">Normal</span>
                                @break
                                @case(1)
                                    <span class="tag is-danger is-medium">Returned</span>
                                @break
                                @case(2)
                                    <span class="tag is-warning is-medium">Semi Returned</span>
                                @break
                            @endswitch
                        </div>
                    </td>
                    @if ($orders_view)
                    <td>
                        <div><a href="{{ route('orders_view', $transaction->id) }}">View</a></div>
                    </td>
                    @endif
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
            "scrollY": "49vh",
            "scrollCollapse": true,
            "order": [],
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    4,
                    @if($orders_view)
                    5
                    @endif
                ]
            }]
        });
        $('#loading').hide();
        $('#order_container').css('visibility', 'visible');
    });
</script>
@endsection