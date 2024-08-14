@extends('layouts.default', ['page' => 'dashboard'])
@section('content')
<h1 class="title has-text-weight-bold">Dashboard</h1>

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
</div>

<div class="box">
    <h4 class="subtitle has-text-weight-bold">Finances</h4>
    <nav class="level">
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
        <div class="level-item has-text-centered">
            <div>
                <p class="heading">Gift card revenue</p>
                <p class="title">{{ $financial['giftCardRevenue'] }}</p>
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
                <p class="heading">Average activity value</p>
                <p class="title">{{ $financial['averageActivityValue'] }}</p>
            </div>
        </div>
    </nav>
</div>

<div class="box">
    {{ $activities }}
</div>

<div class="box">
    {{ $products }}
</div>

<div class="box">
    {{ $giftCards }}
</div>

<div class="box">
    {{ $alerts }}
</div>
@stop
