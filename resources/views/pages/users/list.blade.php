<?php

use App\User;
?>
@extends('layouts.default')
@section('content')
<h2>User List</h2>
<table id="user_list">
    <thead>
        <th>Full Name</th>
        <th>Username</th>
        <th>Balance</th>
        <th>Role</th>
        <th></th>
        <th></th>
    </thead>
    <tbody>
        @foreach (User::all() as $user)
        <tr>
            <td class="table-text">
                <div>{{ $user->full_name }}</div>
            </td>
            <td class="table-text">
                <div>{{ $user->username }}</div>
            </td>
            <td class="table-text">
                <div>${{ number_format($user->balance, 2) }}</div>
            </td>
            <td class="table-text">
                <div>{{ ucfirst($user->role) }}</div>
            </td>
            <td>
                <div><a href="users/info/{{ $user->id }}">Info</a></div>
            </td>
            <td>
                <div><a href="users/edit/{{ $user->id }}">Edit</a></div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#user_list').DataTable();
    });
    $('#user_list').DataTable({
        paging: false,
        "scrollY": "340px",
        "scrollCollapse": true,
    });
</script>
@endsection