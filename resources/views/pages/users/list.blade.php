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
                @isset($cannot_view_users)
                    <div class="notification is-danger is-light">
                        <span>You cannot view users in the current Rotation.</span>
                    </div>
                @else
                    @permission(\App\Helpers\Permission::USERS_LIST_SELECT_ROTATION)
                    <div class="column is-12">
                        <div class="field">
                            <div class="control">
                                <div class="select">
                                    <select name="rotation" class="input" id="rotation">
                                        <option value="*" @if ($user_list_rotation_id === '*') selected @endif>All Rotations</option>
                                        @foreach ($rotations as $rotation)
                                            <option value="{{ $rotation->id }}" @if((int) $user_list_rotation_id === $rotation->id) selected @endif>
                                                {{ $rotation->name }} @if($rotation->isPresent()) (Present) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endpermission

                    <table id="user_list">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Balance</th>
                                <th>Role</th>
                                <th>Rotations</th>
                                @permission(\App\Helpers\Permission::USERS_VIEW)
                                    <th></th>
                                @endpermission
                                @permission(\App\Helpers\Permission::USERS_MANAGE)
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
                                    <div>{{ $user->balance }}</div>
                                </td>
                                <td>
                                    <div>{{ $user->role->name }}</div>
                                </td>
                                <td>
                                    @php
                                        echo implode(', ', $user->rotations->pluck('name')->toArray());
                                    @endphp
                                </td>
                                @permission(\App\Helpers\Permission::USERS_VIEW)
                                <td>
                                    <div><a href="{{ route('users_view', $user) }}">View</a></div>
                                </td>
                                @endpermission
                                @permission(\App\Helpers\Permission::USERS_MANAGE)
                                @if (Auth::user()->role->canInteract($user->role))
                                    <td>
                                        <div><a href="{{ route('users_edit', $user->id) }}">Edit</a></div>
                                    </td>
                                @else
                                    <td>
                                        <div class="control">
                                            <button class="button is-light" disabled>
                                                <span class="icon">
                                                    🔒
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
                @endisset
            </div>
        </div>
    </div>
</div>
<script>
    @unless(isset($cannot_view_users))
        @permission(\App\Helpers\Permission::USERS_LIST_SELECT_ROTATION)
        $('#rotation').change(function() {
            document.cookie = "user_list_rotation_id=" + $(this).val();
            location.reload();
        });
        @endpermission

        $('#user_list').DataTable({
            "paging": false,
            "scrollY": "49vh",
            "scrollCollapse": true,
            "columnDefs": [{
                "orderable": false,
                "searchable": false,
                "targets": [
                    4,
                    @if(hasPermission(\App\Helpers\Permission::USERS_VIEW) && hasPermission(\App\Helpers\Permission::USERS_MANAGE))
                        5,
                        6
                    @elseif(hasPermission(\App\Helpers\Permission::USERS_VIEW) && !hasPermission(\App\Helpers\Permission::USERS_MANAGE))
                        5
                    @elseif(!hasPermission(\App\Helpers\Permission::USERS_VIEW) && hasPermission(\App\Helpers\Permission::USERS_MANAGE))
                        5
                    @endif
                ]
            }]
        });
    @endunless

    $('#loading').hide();
    $('#user_container').css('visibility', 'visible');
</script>
@endsection
