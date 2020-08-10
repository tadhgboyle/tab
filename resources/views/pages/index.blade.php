@php

use App\User;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Auth;
@endphp
@extends('layouts.default')
@section('content')
<h2><strong>Cashier</strong></h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-8" id="user_container" style="visibility: hidden;">
        @include('includes.messages')
        <table id="user_list">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach(User::where('deleted', false)->when(!SettingsController::getSelfPurchases(), function ($query) { if (Auth::user()->role != 'administrator') { return $query->where('id', '<>', Auth::user()->id); }})->get() as $result)
                    <tr>
                        <td class="table-text">
                            <div><a href="orders/{{ $result->id }}">{{ $result->full_name }}</a></div>
                        </td>
                        <td class="table-text">
                            <div>${{ number_format($result->balance, 2) }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-md-2">
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "28vw",
            "scrollCollapse": true,
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": 1
                }
            ]
        });
        $('#loading').hide();
        $('#user_container').css('visibility', 'visible');
    });
</script>
@stop