@extends('layouts.default')
@section('content')
@php
use App\Http\Controllers\SettingsController;
@endphp
<h2>Settings</h2>
<div class="row">
    <div class="col-md-4">
        <br>
        @include('includes.messages')
        <form action="/settings/submit" id="settings" method="POST">
            @csrf
            <span>GST</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">%</div>
                </div>
                <input type="number" step="0.01" name="gst" class="form-control" placeholder="GST"
                    value="{{ SettingsController::getGst() }}">
            </div>
            <span>PST</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">%</div>
                </div>
                <input type="number" step="0.01" name="pst" class="form-control" placeholder="PST"
                    value="{{ SettingsController::getPst() }}">
            </div>
            <span>Staff Discount</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">%</div>
                </div>
                <input type="number" step="0.01" name="staff_discount" class="form-control"
                    placeholder="Staff Discount (Not working yet)" value="{{ SettingsController::getStaffDiscount() }}">
            </div>
            <p></p>
            <button type="submit" class="btn btn-success">Save</button>
    </div>
    <div class="col-md-4">
        <br>
        <span title="Allow Cashiers + Managers to ring up orders for themselves.">Self Purchases</span>
        <input type="checkbox" name="self_purchases" @if(SettingsController::getSelfPurchases()) checked @endif>
        </form>
    </div>
    <div class="col-md-4">
        <table id="category_list">
            <thead>
                <tr>
                    <th>Categories</th>
                    <th></th>
                </tr>
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
                                <a href="javascript:;" data-toggle="modal"
                                    onclick="deleteData('{{ $category->value }}')" data-target="#DeleteModal">Delete</a>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p></p>
        <input type="submit" onclick="window.location='settings/category/new';" value="New"
            class="btn btn-xs btn-success">
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
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal"
                            onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#category_list').DataTable({
            "paging": false,
            "searching": false,
            "scrollY": "26vw",
            "scrollCollapse": true,
            "bInfo": false,
        });
    });

    function deleteData(category_name) {
        let name = document.getElementById(category_name).value;
        let url = '{{ route("delete_category", ":name") }}';
        url = url.replace(':name', name);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
</script>
@stop