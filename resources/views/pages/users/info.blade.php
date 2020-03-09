<?php

use App\Transactions;
?>
@extends('layouts.default')
@section('content')
<h2>User Info</h2>
<p>User: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }} <a href="/users/edit/{{ request()->route('id') }}">(Edit)</a></p>
<span>Total spent: ${{ number_format(Transactions::where('purchaser_id', request()->route('id'))->sum('total_price'), 2) }}</span>,&nbsp;
<span>Total returned: ${{ number_format(Transactions::where([['purchaser_id', request()->route('id')], ['status', '1']])->sum('total_price'), 2) }}</span>,&nbsp;
<span>Total owing: ${{ number_format(Transactions::where([['purchaser_id', request()->route('id')], ['status', '0']])->sum('total_price'), 2) }}</span>
<br>
<br>
<div class="row">
    <div class="col-md-8">
        <h3>History</h3>
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
    </div>
    <div class="col-md-4" align="center">
        <h3>Categories</h3>
        <?php

        use App\Http\Controllers\SettingsController;
        use App\Http\Controllers\UserLimitsController;
        ?>
        <table id="category_list">
            <thead>
                <th>Category</th>
                <th>Limit</th>
                <th>Spent</th>
                <th>Remaining</th>
            </thead>
            <tbody>
                @foreach(SettingsController::getCategories() as $category)
                <?php
                $category_limit = UserLimitsController::findLimit(request()->route('id'), $category->value);
                $category_spent = UserLimitsController::findSpent(request()->route('id'), $category->value);
                ?>
                <tr>
                    <td class="table-text">
                        <div>{{ ucfirst($category->value) }}</div>
                    </td>
                    <td class="table-text">
                        <div>{!! $category_limit == "-1" ? "<i>Unlimited</i>" : "$" . number_format($category_limit, 2) . "/" . UserLimitsController::findDuration(request()->route('id'), $category->value) !!}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ number_format($category_spent, 2) }}</div>
                    </td>
                    <td class="table-text">
                        <div>{!! $category_limit == "-1" ? "<i>Unlimited</i>" : "$" . number_format($category_limit - $category_spent, 2) !!}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#order_list').DataTable();
    });
    $('#order_list').DataTable({
        "order": [
            [0, "desc"]
        ],
        paging: false,
        searching: false,
        "scrollY": "245px",
        "scrollCollapse": true,
    });
    $(document).ready(function() {
        $('#category_list').DataTable();
    });
    $('#category_list').DataTable({
        searching: false,
        paging: false,
        bInfo: false,
    });
</script>
@endsection