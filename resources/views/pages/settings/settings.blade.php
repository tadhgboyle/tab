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
            <!-- TODO: this -->
            Staff Discount<input type="number" step="0.01" name="staff_discount" class="form-control" placeholder="Staff Discount" value="{{ SettingsController::getStaffDiscount() }}">
            <p></p>
            <button type="submit" class="btn btn-xs btn-success">Save Settings</button>
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
                        <div>
                            <form>
                                <input type="hidden" id="{{ $category->value }}" value="{{ $category->value }}">
                                <a href="javascript:;" data-toggle="modal" onclick="deleteData('{{ $category->value }}')" data-target="#DeleteModal">Delete</a>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p></p>
        <input type="submit" onclick="window.location='settings/category/new';" value="New Category" class="btn btn-xs btn-success">
    </div>
    <div class="col-md-2">
    </div>
</div>
<div id="DeleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <form action="" id="deleteForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    @csrf
                    <p class="text-center">Are you sure you want to delete this category?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal" onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#category_list').DataTable();
    });
    $('#category_list').DataTable({
        "paging": false,
        "searching": false,
        "scrollY": "26vw",
        "scrollCollapse": true,
        "bInfo": false,
    });

    function deleteData(category_name) {
        let name = document.getElementById(category_name).value;
        console.log(name);
        let url = '{{ route("delete_category", ":name") }}';
        url = url.replace(':name', name);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
</script>
@stop