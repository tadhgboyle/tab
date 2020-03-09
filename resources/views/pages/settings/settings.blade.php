@extends('layouts.default')
@section('content')
<h2>Settings</h2>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
    @include('includes.messages')
        <form action="/settings/submit" method="POST">
            @csrf
            <?php

            use App\Http\Controllers\SettingsController;
            ?>
            GST<input type="number" step="0.01" name="gst" class="form-control" placeholder="GST" value="{{ SettingsController::getGst() }}">
            PST<input type="number" step="0.01" name="pst" class="form-control" placeholder="PST" value="{{ SettingsController::getPst() }}">
            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
        <table id="category_list">
            <thead>
                <th>Category</th>
                <th></th>
            </thead>
            <tbody>
                @foreach(SettingsController::getCategories() as $category)
                <tr>
                    <td class="table-text">
                        <div>{{ ucfirst($category->value) }}</div>
                    </td>
                    <td class="table-text">
                        <div><a href="settings/category/delete/{{ $category->id }}">Delete</a></div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <input type="submit" onclick="window.location='settings/category/new';" value="New Category">
    </div>
    <div class="col-md-2">
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#category_list').DataTable();
    });
    $('#category_list').DataTable({
        paging: false,
        searching: false,
        "scrollY": "350px",
        "scrollCollapse": true,
        bInfo: false,
    });
</script>
@stop