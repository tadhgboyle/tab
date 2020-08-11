@php
use App\Http\Controllers\StatisticsChartController;
use App\Http\Controllers\SettingsController;

$lookBack = SettingsController::getLookBack();
$orderinfo = StatisticsChartController::orderInfo($lookBack);
$iteminfo = StatisticsChartController::itemInfo($lookBack);
@endphp
@extends('layouts.default', ['page' => 'statistics'])
@section('content')
<h2 class="title has-text-weight-bold">Statistics</h2>
<div class="columns is-multiline box">
    <div class="column is-12">
        @include('includes.messages')
        <div class="field">
            <div class="control">
                <div class="select">
                    <form action="/statistics" id="edit_loopback" method="POST">
                        @csrf
                        <select name="lookback" class="input" id="lookback">
                            <option value="9999" {{ $lookBack == "9999" ? "selected" : "" }}>All Time</option>
                            <option value="90" {{ $lookBack == "90" ? "selected" : "" }}>Three Months</option>
                            <option value="30" {{ $lookBack == "30" ? "selected" : "" }}>One Month</option>
                            <option value="14" {{ $lookBack == "14" ? "selected" : "" }}>Two Weeks</option>
                            <option value="7" {{ $lookBack == "7" ? "selected" : "" }}>One Week</option>
                            <option value="2" {{ $lookBack == "2" ? "selected" : "" }}>Two Days</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-half">
        <h3>Order Info</h3>
        <div>
            {!! $orderinfo->container() !!}
        </div>
    </div>
    <div class="column is-half">
        <h3>Item Info</h3>
        <div>
            {!! $iteminfo->container() !!}
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
{!! $orderinfo->script() !!}
{!! $iteminfo->script() !!}

<!-- Handle changing the lookBack dropdown -->
<script>
    $('#lookback').change(function () {
            this.form.submit();
        }
    )
</script>

@endsection