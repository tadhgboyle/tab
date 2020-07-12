<?php
use App\User;
?>
@extends('layouts.default')
@section('content')
<h2>User List</h2>

<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        @include('includes.messages')
    </div>
    <div class="col-md-3"></div>
</div>

<table id="user_list">
    <thead>
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Balance</th>
            <th>Role</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach (User::where('deleted', false)->get() as $user)
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
        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "50vh",
            "scrollCollapse": true,
        });
    });
</script>
@endsection