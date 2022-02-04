@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @if($user->trashed()) <strong>(Deleted)</strong> @endif @if(!$user->trashed() && hasPermission('users_manage') && $can_interact)<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif</h4>
<p><strong>Role:</strong> {{ $user->role->name }}</p>
<span><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}, </span>
<span><strong>Total spent:</strong> ${{ number_format($user->findSpent(), 2) }}, </span>
<span><strong>Total returned:</strong> ${{ number_format($user->findReturned(), 2) }}, </span>
<span><strong>Total owing:</strong> ${{ number_format($user->findOwing(), 2) }}</span>

<br>
<br>

<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>

<div class="columns" id="table_container" style="visibility: hidden;">

    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <div class="box">
                    <h4 class="title has-text-weight-bold is-4">Order History</h4>
                    <table id="order_list">
                        <thead>
                            <th>Time</th>
                            <th>Cashier</th>
                            <th>Price</th>
                            <th>Status</th>
                            @permission('orders_view')
                            <th></th>
                            @endpermission
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <div>{{ $transaction->created_at->format('M jS Y h:ia') }}</div>
                                </td>
                                <td>
                                    <div>{{ $transaction->cashier->full_name }}</div>
                                </td>
                                <td>
                                    <div>${{ number_format($transaction->total_price, 2) }}</div>
                                </td>
                                <td>
                                    <div>
                                        @switch($transaction->getReturnStatus())
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
                                @permission('orders_view')
                                <td>
                                    <div><a href="{{ route('orders_view', $transaction->id) }}">View</a></div>
                                </td>
                                @endpermission
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="column">
                <div class="box">
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
                            @foreach($activity_transactions as $transaction)
                            <tr>
                                <td>
                                    <div>{{ $transaction['created_at']->format('M jS Y h:ia') }}</div>
                                </td>
                                <td>
                                    <div>{{ $transaction['cashier']->full_name }}</div>
                                </td>
                                <td>
                                    <div>
                                        @permission('activities_view')
                                            <a href="{{ route('activities_view', $transaction['activity']->id) }}">{{ $transaction['activity']->name }}</a>
                                        @else
                                            {{ $transaction['activity']->name }}
                                        @endpermission
                                    </div>
                                </td>
                                <td>
                                    <div>{!! $transaction['price'] > 0 ? '$' . number_format($transaction['price'], 2) : '<i>Free</i>' !!}</div>
                                </td>
                                <td>
                                    <div>
                                        @switch($transaction['returned'])
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

    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <div class="box">
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
                            @foreach($categories as $category)
                                <tr>
                                    <td>
                                        <div>{{ $category['name'] }}</div>
                                    </td>
                                    <td>
                                        <div>{!! $category['limit'] === -1.0 ? "<i>Unlimited</i>" : "$" . number_format($category['limit'], 2) . "/" . $category['duration'] !!}</div>
                                    </td>
                                    <td>
                                        <div>${{ number_format($category['spent'], 2) }}</div>
                                    </td>
                                    <td>
                                        <div>{!! $category['limit'] === -1.0 ? "<i>Unlimited</i>" : "$" . number_format($category['limit'] - $category['spent'], 2) !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <h4 class="title has-text-weight-bold is-4">Rotations</h4>
                    <table id="rotation_list">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rotations as $rotation)
                                <tr>
                                    <td>
                                        <div>{{ $rotation->name }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $rotation->start->format('M jS Y h:ia') }}</div>
                                    </td>
                                    <td>
                                        <div>{{ $rotation->end->format('M jS Y h:ia') }}</div>
                                    </td>
                                    <td>
                                        <div>{!! $rotation->getStatusHtml() !!}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
                    @permission('orders_view')
                    4
                    @endpermission
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
        $('#rotation_list').DataTable({
            "searching": false,
            "paging": false,
            "bInfo": false,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [0, 1, 2]
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
