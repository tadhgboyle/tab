@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">User List</h2>
<div id="loading" align="center">
    <img src="{{ url('img/loader.gif') }}" alt="Loading..." class="loading-spinner" />
</div>
<div class="columns">
    <div class="column" id="user_container" style="visibility: hidden;">
        <div>
            <div class="box">
                @include('includes.messages')
                <table id="user_list">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Balance</th>
                            <th>Role</th>
                            @permission('users_view')
                                <th></th>
                            @endpermission
                            @permission('users_manage')
                                <th></th>
                            @endpermission
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div>{{ $user->full_name }}</div>
                            </td>
                            <td>
                                <div>{{ $user->username }}</div>
                            </td>
                            <td>
                                <div>${{ number_format($user->balance, 2) }}</div>
                            </td>
                            <td>
                                <div>{{ $user->role->name }}</div>
                            </td>
                            @permission('users_view')
                            <td>
                                <div><a href="{{ route('users_view', $user) }}">View</a></div>
                            </td>
                            @endpermission
                            @permission('users_manage')
                            @if (Auth::user()->role->canInteract($user->role))
                                <td>
                                    <div><a href="{{ route('users_edit', $user->id) }}">Edit</a></div>
                                </td>
                            @else
                                <td>
                                    <div class="control">
                                        <button class="button is-warning" disabled>
                                        <span class="icon">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        </button>
                                    </div>
                                </td>
                            @endif
                            @endpermission
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    window.onload = function() {
        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    @if(hasPermission('users_view') && hasPermission('users_manage'))
                    4,
                    5
                    @elseif(hasPermission('users_view') && !hasPermission('users_manage'))
                    4
                    @elseif(!hasPermission('users_view') && hasPermission('users_manage'))
                    4
                    @endif
                ]
            }]
        });
        $('#loading').hide();
        $('#user_container').css('visibility', 'visible');
    };
</script>
@endsection
