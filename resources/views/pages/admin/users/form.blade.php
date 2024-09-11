@extends('layouts.default', ['page' => 'users'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($user) ? 'Edit' : 'Create' }} User</h2>
@isset($user)<h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @permission(\App\Helpers\Permission::USERS_VIEW)<a href="{{ route('users_view', $user->id) }}">(View)</a>@endpermission</h4>@endisset
<form action="{{ isset($user) ? route('users_update', $user->id) : route('users_store') }}" id="user_form" method="POST">
    @csrf

    @isset($user)
        @method('PUT')
        <input type="hidden" name="user_id" value="{{ $user->id }}">
    @endisset

    <div class="columns">
        <div class="column is-5">
            <div class="box">
                <h4 class="title has-text-weight-bold is-4">General</h4>
                <div class="field">
                    <label class="label">Full Name<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="text" name="full_name" class="input" required placeholder="Full Name" value="{{ $user->full_name ?? old('full_name') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Username</label>
                    <div class="control has-icons-left">
                        <span class="icon is-small is-left">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" name="username" class="input" placeholder="Username (Optional)" value="{{ $user->username ?? old('username') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Balance</label>
                    <div class="control has-icons-left">
                        <span class="icon is-small is-left">
                            <i class="fas fa-dollar-sign"></i>
                        </span>
                        <input type="number" step="0.01" name="balance" class="input money-input" placeholder="Balance" value="{{ (isset($user) ? $user->balance->formatForInput() : null) ?? number_format(old('balance'), 2, '.', '') }}">
                    </div>
                </div>

                <div class="field">
                    <label class="label">Rotations<sup style="color: red">*</sup></label>
                    <div class="select is-multiple is-fullwidth">
                        <select multiple size="4" name="rotations[]">
                            @foreach ($rotations as $rotation)
                                <option value="{{ $rotation->id }}" @if (isset($user) && $user->rotations->contains($rotation)) selected  @endif>
                                    {{ $rotation->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Role<sup style="color: red">*</sup></label>
                    <!-- TODO: some sort of blocking of changing their own role -->
                    <div class="control">
                        <div class="select is-fullwidth" id="role_id">
                            <select name="role_id" class="input" required>
                                @foreach($available_roles as $role)
                                    <option value="{{ $role->id }}" data-staff="{{ $role->staff ? 1 : 0 }}" {{ (isset($user->role) && $user->role->id === $role->id) || old('role') === $role->id ? "selected" : "" }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div id="password_hideable" style="display: none;">
                    <div class="field">
                        <label class="label">{{ isset($user) ? 'Change ' : '' }}Password<sup style="color: red">*</sup></label>
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password" class="input" placeholder="Password" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="field">
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" name="password_confirmation" class="input" placeholder="Confirm Password" autocomplete="new-password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="column">
            <div class="box">
                <h4 class="title has-text-weight-bold is-4">Category Limits</h4>

                @foreach($categories as $category)
                    <div class="field">
                        <label class="label">{{ $category['name'] }} Limit</label>
                        <div class="control has-icons-left">
                            <span class="icon is-small is-left">
                                <i class="fas fa-dollar-sign"></i>
                            </span>
                            <input type="number" step="0.01" name="limits[{{ $category['id'] }}]" class="input money-input" placeholder="Limit" value="{{ isset($user) ? $category['limit']->limit->formatForInput() : "0.00"}}">
                        </div>
                        <div class="control">
                            <label class="radio">
                                <input type="radio" name="durations[{{ $category['id'] }}]" value="0" @if((isset($user) && $category['limit']->duration() === "day") || !isset($user)) checked @endif>
                                Day
                            </label>
                            <label class="radio">
                                <input type="radio" name="durations[{{ $category['id'] }}]" value="1" @if(isset($user) && $category['limit']->duration() === "week") checked @endif>
                                Week
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="column is-2">
            <form>
                <div class="control">
                    <button class="button is-light" type="submit" form="user_form">
                        💾 Submit
                    </button>
                </div>
            </form>
            <br>
            @isset($user)
            <div class="control">
                <form>
                    <button class="button is-danger is-outlined" type="button" onclick="openModal();">
                        <span>Delete</span>
                        <span class="icon is-small">
                            <i class="fas fa-times"></i>
                        </span>
                    </button>
                </form>
            </div>
            @endisset
        </div>
    </div>
</form>

@isset($user)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the user {{ $user->full_name }}?</p>
            <form action="{{ route('users_delete', $user) }}" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endisset

<script type="text/javascript">
    $(document).ready(function() {
        updatePassword($("option:selected", document.getElementById('role')).data('staff'));
    });

    $('select').on('change', function() {
        updatePassword($("option:selected", this).data('staff'))
    });

    function updatePassword(staff) {
        if (staff !== undefined) {
            const div = $('#password_hideable');
            if (staff) {
                div.fadeIn(200);
            } else {
                div.fadeOut(200);
            }
        }
    }

    @isset($user)
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    @endisset
</script>
@endsection
