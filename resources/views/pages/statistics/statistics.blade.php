@php
use App\Http\Controllers\StatisticsChartController;
use App\Http\Controllers\SettingsController;

$statsTime = SettingsController::getStatsTime();
$orderinfo = StatisticsChartController::orderInfo($statsTime);
$iteminfo = StatisticsChartController::itemInfo($statsTime);
$profit = StatisticsChartController::profit($statsTime);
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
                    <form action="/statistics" method="POST">
                        @csrf
                        <select name="stats_time" class="input" id="stats_time">
                            <option value="9999" {{ $statsTime == "9999" ? "selected" : "" }}>All Time</option>
                            <option value="90" {{ $statsTime == "90" ? "selected" : "" }}>Three Months</option>
                            <option value="30" {{ $statsTime == "30" ? "selected" : "" }}>One Month</option>
                            <option value="14" {{ $statsTime == "14" ? "selected" : "" }}>Two Weeks</option>
                            <option value="7" {{ $statsTime == "7" ? "selected" : "" }}>One Week</option>
                            <option value="2" {{ $statsTime == "2" ? "selected" : "" }}>Two Days</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-half">
        <h4 class="title has-text-weight-bold is-4">Order Info</h4>
        <div>
            {!! $orderinfo->container() !!}
        </div>
    </div>
    <div class="column is-half">
        <h4 class="title has-text-weight-bold is-4">Item Info</h4>
        <div>
            {!! $iteminfo->container() !!}
        </div>
    </div>
    <div class="column is-half">
        <h4 class="title has-text-weight-bold is-4">Profit</h4>
        <div>
            {!! $profit->container() !!}
        </div>
    </div>
    <div class="column is-half"></div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
{!! $orderinfo->script() !!}
{!! $iteminfo->script() !!}
{!! $profit->script() !!}

<script>
    $('#stats_time').change(function () {
            this.form.submit();
        }
    )
</script>

@endsection