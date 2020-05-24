@extends('layouts.default')
@section('content')
<h2>Statistics</h2>
@php
    use App\Http\Controllers\StatisticsChartController;
    $recentorders = StatisticsChartController::recentOrders();
    $popularitems = StatisticsChartController::popularItems();
@endphp
<div class="row">
    <div class="col-md-6">
        <h3>Recent Orders</h3>
        <div>
        {!! $recentorders->container() !!}
        </div>
    </div>
    <div class="col-md-6">
        <h3>Popular Items</h3>
        <div>
            {!! $popularitems->container() !!}
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js" charset="utf-8"></script>
{!! $recentorders->script() !!}
{!! $popularitems->script() !!}
@endsection