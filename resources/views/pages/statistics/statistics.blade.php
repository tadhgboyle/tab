@extends('layouts.default', ['page' => 'statistics'])
@section('content')
<h2 class="title has-text-weight-bold">Statistics</h2>
<div class="columns is-multiline box">
    @permission('statistics_select_rotation')
    <div class="column is-12">
        <div class="field">
            <div class="control">
                <div class="select">
                    <select name="rotation" class="input" id="rotation">
                        <option value="*" @if ($stats_rotation_id === '*') selected @endif>All Rotations</option>
                        @foreach ($rotations as $rotation)
                            <option value="{{ $rotation->id }}" @if ((int) $stats_rotation_id === $rotation->id) selected @endif>
                                {{ $rotation->name }} @if($rotation->isPresent()) (Present) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    @endpermission
    @isset($cannot_view_statistics)
        <div class="notification is-danger is-light">
            <span>There are no statistics available to view in your Rotation.</span>
        </div>
    @else
        <div class="column is-half">
            @permission('statistics_order_history')
            <h4 class="title has-text-weight-bold is-4">Order Info</h4>
            <div>
                <div id="order_history_chart" style="height: 400px;"></div>
            </div>
            @endpermission
        </div>
        <div class="column is-half">
            @permission('statistics_product_sales')
            <h4 class="title has-text-weight-bold is-4">Product Sales</h4>
            <h6 class="subtitle">(Only top 50 products are shown)</h6>
            <div>
                <div id="product_sales_chart" style="height: 400px;"></div>
            </div>
            @endpermission
        </div>
        <div class="column is-half">
            @permission('statistics_activity_sales')
            <h4 class="title has-text-weight-bold is-4">Activity Sales</h4>
            <div>
                <div id="activity_sales_chart" style="height: 400px;"></div>
            </div>
            @endpermission
        </div>
        <div class="column is-half">
            @permission('statistics_income_info')
            <h4 class="title has-text-weight-bold is-4">Income Info</h4>
            <div>
                <div id="income_info_chart" style="height: 400px;"></div>
            </div>
            @endpermission
        </div>
    @endisset
</div>

<script src="https://unpkg.com/echarts/dist/echarts.min.js"></script>
<script src="https://unpkg.com/@chartisan/echarts/dist/chartisan_echarts.js"></script>

<script>
    @unless(isset($cannot_view_statistics))
        @permission('statistics_order_history')
            new Chartisan({
                el: '#order_history_chart',
                url: "@chart('order_history_chart')",
                hooks: new ChartisanHooks()
                    .legend()
                    .colors(['#22577A', '#38A3A5'])
                    .tooltip()
            });
        @endpermission

        @permission('statistics_product_sales')
            new Chartisan({
                el: '#product_sales_chart',
                url: "@chart('product_sales_chart')",
                hooks: new ChartisanHooks()
                    .legend()
                    .tooltip()
            });
        @endpermission

        @permission('statistics_activity_sales')
            new Chartisan({
                el: '#activity_sales_chart',
                url: "@chart('activity_sales_chart')",
                hooks: new ChartisanHooks()
                    .legend()
                    .colors(['#9B9ECE'])
                    .tooltip()
            });
        @endpermission

        @permission('statistics_income_info')
        new Chartisan({
            el: '#income_info_chart',
            url: "@chart('income_info_chart')",
            hooks: new ChartisanHooks()
                .legend()
                .datasets([{ type: 'line', fill: false }, 'bar'])
                .colors(['#512D38', '#B27092'])
                .tooltip()
        });
        @endpermission

        @permission('statistics_select_rotation')
        $('#rotation').change(function() {
            document.cookie = "stats_rotation_id=" + $(this).val();
            location.reload();
        });
        @endpermission
    @endunless
</script>

@endsection
