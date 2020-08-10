@extends('layouts.default')
@section('content')
<h2><strong>User Info</strong></h2>
@php
use App\Transactions;
use App\Http\Controllers\OrderController;
use App\User;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserLimitsController;

$user = User::find(request()->route('id'));
if ($user == null) return redirect('/users')->with('error', 'Invalid user.')->send();
@endphp
<p><strong>User:</strong> {{ $user->full_name }} @if(!$user->deleted)<a href="/users/edit/{{ $user->id }}">(Edit)</a>@endif</p>
<p><strong>Role:</strong> {{ ucfirst($user->role) }}</p>
<p><strong>Deleted:</strong> {{ $user->deleted ? 'True' : 'False' }}</p>
<span><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}, </span>
<span><strong>Total spent:</strong> ${{ User::findSpent($user) }}, </span>
<span><strong>Total returned:</strong> ${{ User::findReturned($user) }}, </span>
<span><strong>Total owing:</strong> ${{ User::findOwing($user) }}</span>
<br>
<br>

<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="row" id="table_container" style="visibility: hidden;">
    <div class="col-md-7">
        <h3><strong>History</strong></h3>
        <table id="order_list">
            <thead>
                <th>Time</th>
                <th>Cashier</th>
                <th>Price</th>
                <th>Status</th>
                <th></th>
            </thead>
            <tbody>
                @foreach (Transactions::where('purchaser_id', $user->id)->orderBy('created_at', 'DESC')->get() as
                $transaction)
                <tr>
                    <td class="table-text">
                        <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td class="table-text">
                        <div>{{ User::find($transaction->cashier_id)->full_name }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ number_format($transaction->total_price, 2) }}</div>
                    </td>
                    <td class="table-text">
                        <div>
                            {!! !OrderController::checkReturned($transaction->id)
                            ? "<h5><span class=\"badge badge-success\">Normal</span></h5>"
                            : "<h5><span class=\"badge badge-danger\">Returned</span></h5>" !!}
                        </div>
                    </td>
                    <td class="table-text">
                        <div><a href="/orders/view/{{ $transaction->id }}">View</a></div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-5">
        <h3><strong>Limits</strong></h3>
        <table id="category_list">
            <thead>
                <th>Category</th>
                <th>Limit</th>
                <th>Spent</th>
                <th>Remaining</th>
            </thead>
            <tbody>
                @foreach(SettingsController::getCategories() as $category)
                    @php
                    $category_limit = UserLimitsController::findLimit(request()->route('id'), $category->value);
                    $category_duration = UserLimitsController::findDuration(request()->route('id'), $category->value);
                    $category_spent = UserLimitsController::findSpent(request()->route('id'), $category->value, $category_duration);
                    @endphp
                    <tr>
                        <td class="table-text">
                            <div>{{ ucfirst($category->value) }}</div>
                        </td>
                        <td class="table-text">
                            <div>
                                {!! $category_limit == "-1" ? "<i>Unlimited</i>" : "$" . number_format($category_limit, 2) . "/" . $category_duration !!}
                            </div>
                        </td>
                        <td class="table-text">
                            <div>${{ number_format($category_spent, 2) }}</div>
                        </td>
                        <td class="table-text">
                            <div>
                                {!! $category_limit == "-1" ? "<i>Unlimited</i>" : "$" . number_format($category_limit - $category_spent, 2) !!}
                            </div>
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
            "order": [],
            "paging": false,
            "searching": false,
            "scrollY": "28vh",
            "scrollCollapse": true,
        });
        $('#category_list').DataTable({
            "searching": false,
            "paging": false,
            "bInfo": false,
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
    });
</script>
@endsection