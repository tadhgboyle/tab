@extends('layouts.default')
@section('content')
<h2>Cashier</h2>
<?php

use App\User;
?>
<div class="row">
    <div class="col-md-3"></div>
    <div class="panel-body col-md-6">
        <table id="user_list">
            <thead>
                <th>Full Name</th>
                <th>Balance</th>
            </thead>
            <tbody>
                @foreach(User::all() as $result)
                <tr>
                    <td class="table-text">
                        <div><a href="cashier/order/{{ $result->id }}">{{ $result->full_name }}</div>
                    </td>
                    <td class="table-text">
                        <div>${{ $result->balance }}</div>
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
        paging: false
    });
</script>
@stop