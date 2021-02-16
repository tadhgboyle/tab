@php
use App\User;
@endphp
@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<h2 class="title has-text-weight-bold">Cashier</h2>
<div id="loading" align="center">
    <img src="{{ url('loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns">
    <div class="column"></div>
    <div class="column is-half box" id="user_container" style="visibility: hidden;">
        @include('includes.messages')
        <table id="user_list">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                <!-- TODO: Self purchases permission -->
                @php $users = User::where('deleted', false)->select(['id', 'full_name', 'balance'])->get() @endphp
                @foreach($users as $user)
                    <tr>
                        <td>
                            <div><a href="{{ route('orders_new', $user->id) }}">{{ $user->full_name }}</a></div>
                        </td>
                        <td>
                            <div>${{ number_format($user->balance, 2) }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="column"></div>
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
                    "searchable": false,
                    "targets": 1
                }
            ]
        });
        $('#loading').hide();
        $('#user_container').css('visibility', 'visible');
    });
</script>
@stop