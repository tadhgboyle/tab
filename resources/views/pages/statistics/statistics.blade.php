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
                            <option value="9999" {{ $stats_time == "9999" ? "selected" : "" }}>All Time</option>
                            <option value="90" {{ $stats_time == "90" ? "selected" : "" }}>Three Months</option>
                            <option value="30" {{ $stats_time == "30" ? "selected" : "" }}>One Month</option>
                            <option value="14" {{ $stats_time == "14" ? "selected" : "" }}>Two Weeks</option>
                            <option value="7" {{ $stats_time == "7" ? "selected" : "" }}>One Week</option>
                            <option value="2" {{ $stats_time == "2" ? "selected" : "" }}>Two Days</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="column is-half">
        @permission('statistics_order_history')
        <h4 class="title has-text-weight-bold is-4">Order Info</h4>
        <div>
            <div id="purchase_history_chart" style="height: 400px;"></div>
        </div>
        @endpermission
    </div>
    <div class="column is-half">
        @permission('statistics_item_info')
        <h4 class="title has-text-weight-bold is-4">Item Info</h4>
        <h6 class="subtitle">(Only top 50 products are shown)</h6>
        <div>
            <div id="item_sales_chart" style="height: 400px;"></div>
        </div>
        @endpermission
    </div>
    <div class="column is-half">
        @permission('statistics_activity_info')
        <h4 class="title has-text-weight-bold is-4">Activity Info</h4>
        <div>
            <div id="activity_sales_chart" style="height: 400px;"></div>
        </div>
        @endpermission
    </div>
    <div class="column is-half">
    </div>
</div>

<script src="https://unpkg.com/echarts/dist/echarts.min.js"></script>
<script src="https://unpkg.com/@chartisan/echarts/dist/chartisan_echarts.js"></script>

<script>
    @permission('statistics_order_history')
        new Chartisan({
            el: '#purchase_history_chart',
            url: "@chart('purchase_history_chart')",
            hooks: new ChartisanHooks()
                .legend()
                .tooltip()
        });
    @endpermission

    @permission('statistics_item_info')
        new Chartisan({
            el: '#item_sales_chart',
            url: "@chart('item_sales_chart')",
            hooks: new ChartisanHooks()
                .legend()
                .tooltip()
        });
    @endpermission

    @permission('statistics_item_info')
        new Chartisan({
            el: '#activity_sales_chart',
            url: "@chart('activity_sales_chart')",
            hooks: new ChartisanHooks()
                .legend()
                .tooltip()
        });
    @endpermission
    
    $('#stats_time').change(function() {
        this.form.submit();
    })
</script>

@endsection