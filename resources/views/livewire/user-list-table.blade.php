<div>
    @permission('users_list_select_rotation')
    <div class="field">
        <h4>{{ $selectedRotation }}</h4>
        <div class="control">
            <div class="select">
                <select name="rotation" class="input" id="rotation" wire:model="selectedRotation">
                    <option value="*" @if ($selectedRotation == '*') selected @endif>All Rotations</option>
                    @foreach ($rotations as $rotation)
                        <option value="{{ $rotation->id }}" @if ($selectedRotation == $rotation->id) selected @endif>{{ $rotation->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    @endpermission
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
                            <div><a href="{{ route('users_view', $user->id) }}">View</a></div>
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