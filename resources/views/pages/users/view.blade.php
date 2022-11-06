@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @if($user->trashed()) <strong>(Deleted)</strong> @endif @if(!$user->trashed() && hasPermission(\App\Helpers\Permission::USERS_MANAGE) && $can_interact)<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif</h4>
<p><strong>Role:</strong> {{ $user->role->name }}</p>
<span><strong>Balance:</strong> ${{ number_format($user->balance, 2) }}, </span>
<span><strong>Total spent:</strong> ${{ number_format($user->findSpent(), 2) }}, </span>
<span><strong>Total returned:</strong> ${{ number_format($user->findReturned(), 2) }}, </span>
@php $owing = $user->findOwing(); @endphp
<span><strong>Total owing:</strong> <span style="text-decoration: underline; cursor: help;" onclick="openOwingModal();">${{ number_format($owing, 2) }}</span></span>

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
                            <th>Total Price</th>
                            <th>Status</th>
                            @permission(\App\Helpers\Permission::ORDERS_VIEW)
                            <th></th>
                            @endpermission
                        </thead>
                        <tbody>
                            @foreach($user->transactions->sortByDesc('created_at') as $transaction)
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
                                @permission(\App\Helpers\Permission::ORDERS_VIEW)
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
                                <th>Total Price</th>
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
                                        @permission(\App\Helpers\Permission::ACTIVITIES_VIEW)
                                            <a href="{{ route('activities_view', $transaction['activity']->id) }}">{{ $transaction['activity']->name }}</a>
                                        @else
                                            {{ $transaction['activity']->name }}
                                        @endpermission
                                    </div>
                                </td>
                                <td>
                                    <div>{!! $transaction['total_price'] > 0 ? '$' . number_format($transaction['total_price'], 2) : '<i>Free</i>' !!}</div>
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
{{--                                    TODO: display "none" for limits of $0--}}
                                    <td>
                                        <div>{!! $category['limit'] == -1.0 ? "<i>Unlimited</i>" : "$" . number_format($category['limit'], 2) . "/" . $category['duration'] !!}</div>
                                    </td>
                                    <td>
                                        <div>${{ number_format($category['spent'], 2) }}</div>
                                    </td>
                                    <td>
                                        <div>{!! $category['limit'] == -1.0 ? "<i>Unlimited</i>" : "$" . number_format($category['limit'] - $category['spent'], 2) !!}</div>
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
                            @foreach($user->rotations as $rotation)
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
            <div class="column">
                <div class="box">
                    <div class="columns">
                        <div class="column">
                            <h4 class="title has-text-weight-bold is-4">Payouts</h4>
                        </div>
                        <div class="column">
                            @if(hasPermission(\App\Helpers\Permission::USERS_PAYOUTS_CREATE))
                                <a class="button is-success is-pulled-right" href="{{ route('users_payout_create', $user) }}">
                                    <span class="icon is-small">
                                        <i class="fas fa-file-invoice-dollar"></i>
                                    </span>
                                    <span>Create</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <table id="payouts_list">
                        <thead>
                            <tr>
                                <th>Identifier</th>
                                <th>Amount</th>
                                <th>Cashier</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($user->payouts->sortByDesc('created_at') as $payout)
                            <tr>
                                <td>
                                    <div>{!! $payout->identifier ?? "<i>None</i>" !!}</div>
                                </td>
                                <td>
                                    <div>${{ number_format($payout->amount, 2) }}</div>
                                </td>
                                <td>
                                    <div>{{ $payout->cashier->full_name }}</div>
                                </td>
                                <td>
                                    <div>{{ $payout->created_at->format('M jS Y h:ia') }}</div>
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

<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Owing</p>
        </header>
        <section class="modal-card-body">
            <table class="table is-fullwidth">
                <tbody>
                <tr>
                    <td colspan="2">
                        <strong>Transactions</strong>
                    </td>
                </tr>
                @forelse($user->transactions->sortByDesc('created_at') as $transaction)
                    <tr>
                        <td>
                            <div>Transaction (#{{ $transaction->id }})</div>
                        </td>
                        <td>
                            <div>+${{ number_format($transaction->total_price, 2) }}</div>
                        </td>
                    </tr>
                    @switch($transaction->getReturnStatus())
                        @case(\App\Models\Transaction::STATUS_FULLY_RETURNED)
                            <tr>
                                <td>
                                    <div>Return (#{{ $transaction->id }})</div>
                                </td>
                                <td>
                                    <div>-${{ number_format($transaction->total_price, 2) }}</div>
                                </td>
                            </tr>
                            @break
                        @case(\App\Models\Transaction::STATUS_PARTIAL_RETURNED)
                            <tr>
                                <td>
                                    <div>Partial Return (#{{ $transaction->id }})</div>
                                </td>
                                <td>
                                    <div>-${{ number_format($transaction->getReturnedTotal(), 2) }}</div>
                                </td>
                            </tr>
                            @break
                    @endswitch
                @empty
                    <tr>
                        <td>
                            <div><i>No Transactions</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2">
                        <strong>Activities</strong>
                    </td>
                </tr>
                @forelse($activity_transactions as $activity)
                    <tr>
                        <td>
                            <div>Activity ({{ $activity['activity']['name'] }})</div>
                        </td>
                        <td>
                            <div>+${{ number_format($activity['total_price'], 2) }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <div><i>No Activities</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td colspan="2">
                        <strong>Payouts</strong>
                    </td>
                </tr>
                @forelse($user->payouts->sortByDesc('created_at') as $payout)
                    <tr>
                        <td>
                            <div>Payout @if($payout->identifier !== null) ({{ $payout->identifier }}) @endif</div>
                        </td>
                        <td>
                            <div>-${{ number_format($payout->amount, 2) }}</div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td>
                            <div><i>No Payouts</i></div>
                        </td>
                        <td>
                            <div></div>
                        </td>
                    </tr>
                @endforelse
                <tr>
                    <td></td>
                    <td>
                        <div><strong>&nbsp;&nbsp;${{ number_format($owing, 2) }}</strong></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeModal();">Close</button>
        </footer>
    </div>
</div>

<script>
    const modal = document.querySelector('.modal');

    const openOwingModal = () => {
        modal.classList.add('is-active');
    }

    const closeModal = () => {
        modal.classList.remove('is-active');
    }

    $(document).ready(function() {
        $('#order_list').DataTable({
            "order": [],
            "paging": false,
            "searching": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "bInfo": false,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    3,
                    @permission(\App\Helpers\Permission::ORDERS_VIEW)
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
            "bInfo": false,
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
        $('#payouts_list').DataTable({
            "paging": false,
            "bInfo": false,
            "searching": false,
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
