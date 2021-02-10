@php
use App\Transaction;
use App\Http\Controllers\TransactionController;
use App\User;
use App\Role;
use App\Activity;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\UserLimitsController;
use Carbon\Carbon;
use App\Http\Controllers\ActivityController;

$user = User::find(request()->route('id'));
$users_manage = Auth::user()->hasPermission('users_manage');
$orders_view = Auth::user()->hasPermission('orders_view');
if ($user == null) return redirect()->route('users_list')->with('error', 'Invalid user.')->send();
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @if(!$user->deleted && $users_manage && Auth::user()->role->canInteract($user->role))<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif</h4>
<p><strong>Role:</strong> {{ $user->role->name }}</p>
<p><strong>Deleted:</strong> {{ $user->deleted ? 'Yes' : 'No' }}</p>
<span><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}, </span>
<span><strong>Total spent:</strong> ${{ number_format($user->findSpent(), 2) }}, </span>
<span><strong>Total returned:</strong> ${{ number_format($user->findReturned(), 2) }}, </span>
<span><strong>Total owing:</strong> ${{ number_format($user->findOwing(), 2) }}</span>

<br>
<br>

<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>

<div class="columns" id="table_container" style="visibility: hidden;">

    <div class="column is-6 box">
        <h4 class="title has-text-weight-bold is-4">Order History</h4>
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
                @foreach (Transaction::where('purchaser_id', $user->id)->orderBy('created_at', 'DESC')->get() as $transaction)
                <tr>
                    <td>
                        <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                    </td>
                    <td>
                        <div>{{ $transaction->cashier_id->full_name }}</div>
                    </td>
                    <td>
                        <div>${{ number_format($transaction->total_price, 2) }}</div>
                    </td>
                    <td>
                        <div>
                            @switch($transaction->checkReturned())
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

    <div class="column is-1"></div>

    <div class="column is-5">
        <div class="columns is-multiline">
            <div class="column is-12 box">
                <h4 class="title has-text-weight-bold is-4">Category Limits</h4>
                <table id="category_list">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Limit</th>
                            <th>Spent</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(SettingsHelper::getInstance()->getCategories() as $category)
                            @php
                            $info = UserLimitsController::getInfo($user->id, $category->value);
                            $category_limit = $info->limit_per;
                            $category_duration = $info->duration;
                            $category_spent = UserLimitsController::findSpent($user->id, $category->value, $info);
                            @endphp
                            <tr>
                                <td>
                                    <div>{{ ucfirst($category->value) }}</div>
                                </td>
                                <td>
                                    <div>{!! $category_limit == -1 ? "<i>Unlimited</i>" : "$" . number_format($category_limit, 2) . "/" . $category_duration !!}</div>
                                </td>
                                <td>
                                    <div>${{ number_format($category_spent, 2) }}</div>
                                </td>
                                <td>
                                    <div>{!! $category_limit == -1 ? "<i>Unlimited</i>" : "$" . number_format($category_limit - $category_spent, 2) !!}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="column box">
                <h4 class="title has-text-weight-bold is-4">Activity History</h4>
                <table id="activity_list">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Cashier</th>
                            <th>Activity</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (ActivityController::getUserActivities($user) as $transaction)
                        <tr>
                            <td>
                                <div>{{ $transaction['created_at']->format('M jS Y h:ia') }}</div>
                            </td>
                            <td>
                                <div>{{ $transaction['cashier']->full_name }}</div>
                            </td>
                            <td>
                                <div><a href="{{ route('activities_view', $transaction['activity']->id) }}">{{ $transaction['activity']->name }}</a></div>
                            </td>
                            <td>
                                <div>{!! $transaction['price'] > 0 ? '$' . number_format($transaction['price'], 2) : '<i>Free</i>' !!}</div>
                            </td>
                            <td>
                                <div>
                                    @switch($transaction['status'])
                                        @case(0)
                                            <span class="tag is-success is-medium">Normal</span>
                                        @break
                                        @case(1)
                                            <span class="tag is-danger is-medium">Returned</span>
                                        @break
                                    @endswitch
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<br>

<script>
    $(document).ready(function() {
        $('#order_list').DataTable({
            "order": [],
            "paging": false,
            "searching": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    3,
                    @if($orders_view)
                    4
                    @endif
                ]
            }]
        });
        $('#activity_list').DataTable({
            "order": [],
            "paging": false,
            "searching": false,
            "scrollY": "33vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": 4
            }]
        });
        $('#category_list').DataTable({
            "searching": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": 0
            }]
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
    });
</script>
@endsection