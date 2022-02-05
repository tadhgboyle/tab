@extends('layouts.default', ['page' => 'cashier'])
@section('content')
<h2 class="title has-text-weight-bold">Cashier</h2>
@if (!is_null($currentRotation))
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
@endif
<div class="columns">
    <div class="column"></div>
    @if (!is_null($currentRotation))
        <div class="column is-half box" id="user_container" style="visibility: hidden;">
            @include('includes.messages')
            <table id="user_list">
                <thead>
                    <tr>
                        <th>Full Name</th>
                        <th>Rotations</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div><a href="{{ route('orders_new', $user->id) }}">{{ $user->full_name }}</a></div>
                        </td>
                        <td>
                            <div>
                                @php
                                    echo implode(', ', $user->rotations->pluck('name')->toArray());
                                @endphp
                            </div>
                        </td>
                        <td>
                            <div>${{ number_format($user->balance, 2) }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="notification is-danger is-light">
            <span>You cannot make an order without an active Rotation.</span>
        </div>
    @endif
    <div class="column"></div>
</div>
<script>
    @if (!is_null($currentRotation))
        $(document).ready(function() {
            $('#user_list').DataTable({
                "paging": false,
                "scrollY": "49vh",
                "scrollCollapse": true,
                "columnDefs": [
                    {
                        "orderable": false,
                        "searchable": false,
                        "targets": [1, 2]
                    }
                ]
            });
            $('#loading').hide();
            $('#user_container').css('visibility', 'visible');
        });
    @endif
</script>
@stop
