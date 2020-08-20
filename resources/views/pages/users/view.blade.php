@php
use App\Transactions;
use App\Http\Controllers\OrderController;
use App\User;
use App\Roles;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserLimitsController;

$user = User::find(request()->route('id'));
$users_manage = Roles::hasPermission(Auth::user()->role, 'users_manage');
$orders_view = Roles::hasPermission(Auth::user()->role, 'orders_view');
if ($user == null) return redirect()->route('users_list')->with('error', 'Invalid user.')->send();
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @if(!$user->deleted && $users_manage && Roles::canInteract(Auth::user()->role, $user->role))<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif</h4>
<p><strong>Role:</strong> {{ Roles::idToName($user->role) }}</p>
<p><strong>Deleted:</strong> {{ $user->deleted ? 'Yes' : 'No' }}</p>
<span><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}, </span>
<span><strong>Total spent:</strong> ${{ User::findSpent($user) }}, </span>
<span><strong>Total returned:</strong> ${{ User::findReturned($user) }}, </span>
<span><strong>Total owing:</strong> ${{ User::findOwing($user) }}</span>
<br>
<br>

<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box" id="table_container" style="visibility: hidden;">
    <div class="column is-three-fifths">
    <h4 class="title has-text-weight-bold is-4">History</h4>
        <table id="order_list">
            <thead>
                <th>Time</th>
                <th>Cashier</th>
                <th>Price</th>
                <th>Status</th>
                @if($orders_view)
                    <th></th>
                @endif
            </thead>
            <tbody>
                @foreach (Transactions::where('purchaser_id', $user->id)->orderBy('created_at', 'DESC')->get() as
                $transaction)
                <tr>
                    <td>
                        <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td>
                        <div>{{ User::find($transaction->cashier_id)->full_name }}</div>
                    </td>
                    <td>
                        <div>${{ number_format($transaction->total_price, 2) }}</div>
                    </td>
                    <td>
                        <div>
                            @switch(OrderController::checkReturned($transaction->id))
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
                    @if($orders_view)
                        <td>
                            <div><a href="{{ route('orders_view', $transaction->id) }}">View</a></div>
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="column is-two-fifths">
        <h4 class="title has-text-weight-bold is-4">Limits</h4>
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
                        <td>
                            <div>{{ ucfirst($category->value) }}</div>
                        </td>
                        <td>
                            <div>
                                {!! $category_limit == "-1" ? "<i>Unlimited</i>" : "$" . number_format($category_limit, 2) . "/" . $category_duration !!}
                            </div>
                        </td>
                        <td>
                            <div>${{ number_format($category_spent, 2) }}</div>
                        </td>
                        <td>
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
            "scrollY": "33vh",
            "scrollCollapse": true,
            "columnDefs": [
                { 
                    "orderable": false, 
                    "searchable": false,
                    "targets": [
                        3, 
                        @if($orders_view)
                            4
                        @endif
                    ]
                }
            ]
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