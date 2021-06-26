@extends('layouts.default', ['page' => 'statistics'])
@section('content')
<h2 class="title has-text-weight-bold">Statistics</h2>
<div class="columns is-multiline box">
    <div class="column is-12">
        @include('includes.messages')
        @permission('statistics_select_rotation')
        <div class="field">
            <div class="control">
                <div class="select">
                    <select name="rotation" class="input" id="rotation">
                        <option value="all" @if ($stats_rotation_id == 'all') selected @endif>All Rotations</option>
                        @foreach ($rotations as $rotation)
                            <option value="{{ $rotation->id }}" @if ($stats_rotation_id == $rotation->id) selected @endif>{{ $rotation->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @endpermission
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

    @permission('statistics_select_rotation')
    $('#rotation').change(function(sel) {
        const now = new Date();
        now.setTime(now.getTime() + 1 * 3600 * 1000);
        document.cookie = ("statistics_rotation=" + $(this).val() + "; expires=" + now.toUTCString() + "; path=/");
        location.reload();
        console.log(document.cookie);
    });
    @endpermission
</script>

@endsection