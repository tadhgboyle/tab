@extends('layouts.default')
@section('content')
<h2>Cashier</h2>
<?php

use App\User;
?>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
    @include('includes.messages')
        <table id="user_list">
            <thead>
                <th>Full Name</th>
                <th>Balance</th>
            </thead>
            <tbody>
                @foreach(User::where('deleted', false)->get() as $result)
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
    <div class="col-md-3">
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#user_list').DataTable();
    });
    $('#user_list').DataTable({
        "paging": false,
        "scrollY": "26vw",
        "scrollCollapse": true,
    });
</script>
@stop