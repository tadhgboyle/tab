@php

use App\User;
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">User List</h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns box">
    <div class="column" id="user_container" style="visibility: hidden;">
        @include('includes.messages')
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
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [
                { 
                    "orderable": false, 
                    "targets": [4, 5]
                }
        ]
        });
        $('#loading').hide();
        $('#user_container').css('visibility', 'visible');
    });
</script>
@endsection