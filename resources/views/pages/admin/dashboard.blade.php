@extends('layouts.default', ['page' => 'dashboard'])
@section('content')
<x-page-header title="Dashboard" />

@permission(\App\Helpers\Permission::DASHBOARD_USERS)
<div class="box">
    <h4 class="subtitle has-text-weight-bold">Users</h4>
    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total</p>
                <p class="title">{{ $users['total'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total excluding staff</p>
                <p class="title">{{ $users['totalExcludingStaff'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Active</p>
                <p class="title">{{ $users['active'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Inactive</p>
                <p class="title">{{ $users['inactive'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">New</p>
                <p class="title">{{ $users['new'] }}</p>
            </div>
        </div>
    </nav>

    <table class="table">
        <thead>
            <tr>
                <th>Cashier</th>
                <th>Orders made</th>
                <th>Order revenue</th>
                <th>Activity registrations made</th>
                <th>Activity revenue</th>
                <th>Total revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users['topCashiers'] as $cashier)
            <tr>
                <td>
                    <div>{{ $cashier->full_name }}</div>
                </td>
                <td>
                    <div>{{ $cashier->brokered_orders_count }}</div>
                </td>
                <td>
                    <div>{{ $cashier->brokered_orders_sum_total_price }}</div>
                </td>
                <td>
                    <div>{{ $cashier->brokered_activity_registrations_count }}</div>
                </td>
                <td>
                    <div>{{ $cashier->brokered_activity_registrations_sum_total_price }}</div>
                </td>
                <td>
                    <div>{{ $cashier->total_revenue }}</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Orders</th>
                <th>Order revenue</th>
                <th>Activity registrations</th>
                <th>Activity revenue</th>
                <th>Total revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users['topSpenders'] as $user)
            <tr>
                <td>
                    <div>{{ $user->full_name }}</div>
                </td>
                <td>
                    <div>{{ $user->orders_count }}</div>
                </td>
                <td>
                    <div>{{ $user->orders_sum_total_price }}</div>
                </td>
                <td>
                    <div>{{ $user->activity_registrations_count }}</div>
                </td>
                <td>
                    <div>{{ $user->activity_registrations_sum_total_price }}</div>
                </td>
                <td>
                    <div>{{ $user->total_revenue }}</div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endpermission

@permission(\App\Helpers\Permission::DASHBOARD_FINANCIAL)
<div class="box">
    <h4 class="subtitle has-text-weight-bold">Finances</h4>
    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total revenue</p>
                <p class="title">{{ $financial['totalRevenue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Gift card revenue</p>
                <p class="title">{{ $financial['giftCardRevenue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Unspent user balance</p>
                <p class="title">{{ $financial['unspentUserBalance'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Unspent gift card balance</p>
                <p class="title">{{ $financial['unspentGiftCardBalance'] }}</p>
            </div>
        </div>
    </nav>

    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Order revenue</p>
                <p class="title">{{ $financial['orderRevenue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Average order value</p>
                <p class="title">{{ $financial['averageOrderValue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Average order cash amount</p>
                <p class="title">{{ $financial['averageCashPaymentValue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Average order gift card amount</p>
                <p class="title">{{ $financial['averageGiftCardValue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total lost to returns</p>
                <p class="title">{{ $financial['returnedOrderRevenue'] }}</p>
            </div>
        </div>
    </nav>

    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Activity revenue</p>
                <p class="title">{{ $financial['activityRevenue'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Average activity value</p>
                <p class="title">{{ $financial['averageActivityValue'] }}</p>
            </div>
        </div>
    </nav>

    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Total payouts</p>
                <p class="title">{{ $financial['totalPayouts'] }}</p>
            </div>
        </div>
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Remaining to be paid out</p>
                <p class="title">{{ $financial['totalRevenue']->subtract($financial['totalPayouts']) }}</p>
            </div>
        </div>
    </nav>

    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Identifier</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financial['recentPayouts'] as $payout)
                <tr>
                    <td>
                        <div>{{ $payout->user->full_name }}</div>
                    </td>
                    <td>
                        <div>{{ $payout->identifier }}</div>
                    </td>
                    <td>
                        <div>{{ $payout->amount }}</div>
                    </td>
                    <td>
                        <div>{{ $payout->created_at }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endpermission

@permission(\App\Helpers\Permission::DASHBOARD_ACTIVITIES)
<div class="box">
    <h4 class="subtitle has-text-weight-bold">Activities</h4>
    <!-- {{ $activities }} -->
    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Registrations</th>
                    <th>Slots available</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities['upcoming'] as $activity)
                <tr>
                    <td>
                        <div>{{ $activity->name }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->registrations_count }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->slotsAvailable() }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->start->diffForHumans() }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Registrations</th>
                    <th>Slots available</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities['mostSignups'] as $activity)
                <tr>
                    <td>
                        <div>{{ $activity->name }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->registrations_count }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->slotsAvailable() }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->start->diffForHumans() }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Revenue</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activities['mostRevenue'] as $activity)
                <tr>
                    <td>
                        <div>{{ $activity->activity->name }}</div>
                    </td>
                    <td>
                        <div>{{ \Cknow\Money\Money::parse($activity->revenue) }}</div>
                    </td>
                    <td>
                        <div>{{ $activity->activity->start->diffForHumans() }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endpermission

@permission(\App\Helpers\Permission::DASHBOARD_PRODUCTS)
<div class="box">
    <h4 class="subtitle has-text-weight-bold">Products</h4>
    <nav class="level">
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Inventory value</p>
                <p class="title">{{ $products['inventoryValue'] }}</p>
            </div>
        </div>
    </nav>

    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Sales</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products['mostSales'] as $product)
                <tr>
                    <td>
                        <div>{{ $product->product->name }}</div>
                    </td>
                    <td>
                        <div>{{ $product->sales }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="box">
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Returns</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products['mostReturns'] as $product)
                <tr>
                    <td>
                        <div>{{ $product->product->name }}</div>
                    </td>
                    <td>
                        <div>{{ $product->returns }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endpermission

@permission(\App\Helpers\Permission::DASHBOARD_GIFT_CARDS)
<div class="box">
    {{ $giftCards }}
</div>
@endpermission

@permission(\App\Helpers\Permission::DASHBOARD_ALERTS)
<div class="box">
    {{ $alerts }}
</div>
@endpermission
@stop
