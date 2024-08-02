@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">View User</h2>
<h4 class="subtitle">
    <strong>User:</strong> {{ $user->full_name }} @if($user->trashed()) <strong>(Deleted)</strong> @endif @if(!$user->trashed() && hasPermission(\App\Helpers\Permission::USERS_MANAGE) && $can_interact)<a href="{{ route('users_edit', $user->id) }}">(Edit)</a>@endif
</h4>

@canImpersonate
    @canBeImpersonated($user)
        <a href="{{ route('impersonate', $user) }}" class="button is-light">
            🕵 Impersonate
        </a>
        <br />
        <br />
    @endCanBeImpersonated
@endCanImpersonate

@php $owing = $user->findOwing(); @endphp

<div class="box">
    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
            <p class="heading">Balance</p>
            <p class="title">{{ $user->balance }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
            <p class="heading">Total spent</p>
            <p class="title">{{ $user->findSpent() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
            <p class="heading">Total returned</p>
            <p class="title">{{ $user->findReturned() }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
            <p class="heading">Total owing</p>
            <p class="title" style="text-decoration: underline; cursor: help;"  onclick="openOwingModal();">{{ $owing }}</p>
            </div>
        </div>
    </nav>
</div>

<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>

<div class="columns" id="table_container" style="visibility: hidden;">
    <div class="column">
        <div class="columns is-multiline">
            <div class="column">
                <div class="box">
                    <div class="columns">
                        <div class="column">
                            <h4 class="title has-text-weight-bold is-4">Orders</h4>
                        </div>
                        <div class="column">
                            @if(hasPermission($is_self ? \App\Helpers\Permission::CASHIER_SELF_PURCHASES : \App\Helpers\Permission::CASHIER_CREATE))
                                <a class="button is-light is-pulled-right is-small" href="{{ route('orders_create', $user) }}">
                                    ➕ Create
                                </a>
                            @endif
                        </div>
                    </div>
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
                                    <div>{{ $transaction->total_price }}</div>
                                </td>
                                <td>
                                    <div>{!! $transaction->getStatusHtml() !!}</div>
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
            <div class="column is-full">
                <div class="box">
                    <h4 class="title has-text-weight-bold is-4">Activity Registrations</h4>
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
                            @foreach($activity_registrations as $registration)
                            <tr>
                                <td>
                                    <div>{{ $registration->created_at->format('M jS Y h:ia') }}</div>
                                </td>
                                <td>
                                    <div>{{ $registration->cashier->full_name }}</div>
                                </td>
                                <td>
                                    <div>
                                        @permission(\App\Helpers\Permission::ACTIVITIES_VIEW)
                                            <a href="{{ route('activities_view', $registration->activity->id) }}">{{ $registration->activity->name }}</a>
                                        @else
                                            {{ $registration->activity->name }}
                                        @endpermission
                                    </div>
                                </td>
                                <td>
                                    <div>{!! $registration->total_price->isNegative() ? '<i>Free</i>' : $registration->total_price !!}</div>
                                </td>
                                <td>
                                    <div>{!! $registration->getStatusHtml() !!}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="column">
                <div class="box">
                    <h4 class="title has-text-weight-bold is-4">Timeline</h4>
                    <x-entity-timeline :timeline="$user->timeline()" />
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
                                        <div>{!! $category['limit']->isNegative() ? "<i>Unlimited</i>" : $category['limit'] . "/" . $category['duration'] !!}</div>
                                    </td>
                                    <td>
                                        <div>{{ $category['spent'] }}</div>
                                    </td>
                                    <td>
                                        <div>{!! $category['limit']->isNegative() ? "<i>Unlimited</i>" : $category['limit']->subtract($category['spent']) !!}</div>
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
                                <a class="button is-light is-pulled-right is-small" href="{{ route('users_payout_create', $user) }}">
                                    ➕ Create
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
                                    <div>{{ $payout->identifier }}</div>
                                </td>
                                <td>
                                    <div>{{ $payout->amount }}</div>
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

<div class="modal" id="owing_modal">
    <div class="modal-background" onclick="closeOwingModal();"></div>
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
                            <div>+{{ $transaction->total_price }}</div>
                        </td>
                    </tr>
                    @switch($transaction->status)
                        @case(\App\Models\Transaction::STATUS_FULLY_RETURNED)
                            <tr>
                                <td>
                                    <div>Return (#{{ $transaction->id }})</div>
                                </td>
                                <td>
                                    <div>-{{ $transaction->total_price }}</div>
                                </td>
                            </tr>
                            @break
                        @case(\App\Models\Transaction::STATUS_PARTIAL_RETURNED)
                            <tr>
                                <td>
                                    <div>Partial Return (#{{ $transaction->id }})</div>
                                </td>
                                <td>
                                    <div>-{{ $transaction->getReturnedTotal() }}</div>
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
                @forelse($activity_registrations as $registration)
                    <tr>
                        <td>
                            <div>Activity ({{ $registration->activity->name }})</div>
                        </td>
                        <td>
                            <div>+{{ $registration->total_price }}</div>
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
                            <div>Payout ({{ $payout->identifier }})</div>
                        </td>
                        <td>
                            <div>-{{ $payout->amount }}</div>
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
                        <div><strong>&nbsp;&nbsp;{{ $owing }}</strong></div>
                    </td>
                </tr>
                </tbody>
            </table>
        </section>
        <footer class="modal-card-foot">
            <button class="button" onclick="closeOwingModal();">Close</button>
            <a class="button is-success" target="_blank" href="{{ route('users_pdf', $user) }}">
                <span class="icon is-small">
                    <i class="fas fa-file"></i>
                </span>
                <span>PDF</span>
            </a>
        </footer>
    </div>
</div>

<script>
    const owingModal = document.getElementById('owing_modal');

    const openOwingModal = () => {
        owingModal.classList.add('is-active');
    }

    const closeOwingModal = () => {
        owingModal.classList.remove('is-active');
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
        });
        $('#loading').hide();
        $('#table_container').css('visibility', 'visible');
    });
</script>
@endsection
