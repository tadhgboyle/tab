@extends('layouts.default', ['page' => 'settings'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($role) ? 'Edit' : 'Create' }} Role</h2>
@isset($role)
    <h4 class="subtitle"><strong>Role:</strong> {{ $role->name }}</h4>
@endisset

<form action="{{ isset($role) ? route('settings_roles_update', $role->id) : route('settings_roles_store') }}" method="POST" id="role_form">
    @csrf
    @isset($role)
        @method('PUT')
        <input type="hidden" name="role_id" value="{{ $role->id }}">
    @endisset

    <div class="columns">
        <div class="column">
            <div class="box">
                @include('includes.messages')
                <div class="field">
                    <label class="label">Name<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="text" name="name" class="input" placeholder="Role Name" value="{{ $role->name ?? old('name') }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="label">Order<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="number" name="order" class="input" placeholder="Role Order" min="1" value="{{ $role->order ?? old('order') }}" required>
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <label class="checkbox label">
                            Staff
                            <input type="checkbox" class="js-switch" name="staff" id="staff" @if(isset($role->staff) && $role->staff) checked @endif>
                        </label>
                    </div>
                </div>
                <div class="field" id="superuser" style="display: none;">
                    <div class="control">
                        <label class="checkbox label">
                            Superuser
                            <input type="checkbox" class="js-switch" name="superuser" id="superuser" @if(isset($role->superuser) && $role->superuser) checked @endif>
                        </label>
                    </div>
                </div>
                <div class="control">
                    <button class="button is-success" type="submit">
                        <span class="icon is-small">
                            <i class="fas fa-save"></i>
                        </span>
                        <span>Save</span>
                    </button>
                    <a class="button is-outlined" href="{{ route('settings') }}">
                        <span>Cancel</span>
                    </a>
                    @isset($role)
                    <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                        <span>Delete</span>
                        <span class="icon is-small">
                            <i class="fas fa-times"></i>
                        </span>
                    </button>
                    @endisset
                </div>
            </div>
        </div>
        <div class="column box is-8" id="permissions_box" style="visibility: hidden;">
            <h4 class="title has-text-weight-bold is-4">Permissions</h4>
            <hr>
            {!! $permissionHelper->renderForm($role ?? null) !!}
        </div>
    </div>
</form>

@isset($role)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p><strong>{{ count($affected_users) }}</strong>@if(count($affected_users) > 1 || count($affected_users) === 0) users @else user @endif currently have this role.</p>
            <!--
                Rules:
                - Only roles which the current user can interact with
                - If the deleted role is not a staff role, only other non-staff roles are shown
                - If the role is a staff role, roles of any type are shown
            -->
            @if(count($available_roles) === 0)
                <strong>No appropriate backup roles. Cannot delete.</strong>
            @else
                <form action="{{ route('settings_roles_delete', $role->id) }}" id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')

                    @if(count($affected_users) > 0)
                        <p>Please select a new role for them to be placed in:</p>
                        <div class="control">
                            <div class="select">
                                <select name="new_role" id="new_role" class="input" required>
                                    @foreach($available_roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                </form>
            @endif
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="deleteForm" @if(count($available_roles) === 0) disabled @endif>Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endisset

<script>
    $(document).ready(() => {
        updateStaffInfo($('input[type=checkbox][name=staff]').prop('checked'));
        if ($('input[type=checkbox][name=superuser]').prop('checked')) {
            updatePermissionSU(true);
        }
        updateSections();
    });

    $('input[type=checkbox][name=staff]').click(function() {
        updateStaffInfo(this.checked)
    });

    $('input[type=checkbox][name=superuser]').click(function() {
        updatePermissionSU(this.checked)
    });

    function updateStaffInfo(staff) {
        if (staff) {
            $(document.getElementById('superuser')).show(200);
            $(document.getElementById('permissions_box')).css({
                opacity: 0.0,
                visibility: 'visible'
            }).animate({
                opacity: 1.0
            });
        } else {
            $(document.getElementById('superuser')).hide(200);
            $(document.getElementById('permissions_box')).css({
                visibility: 'hidden'
            })
        }
    }

    function updatePermissionSU(superuser) {
        $('.permission').each(function() {
            const checkbox = $(this);
            checkbox.prop('checked', superuser);
            checkbox.prop('disabled', superuser)
        });
        updateSections();
    }

    function updateSections() {
        [{!! $permissionHelper->getCategoryKeys() !!}].forEach(element => {
            if ($(`#permission-${element}-checkbox`).prop('checked')) {
                // TODO: is it better UX to not hide/show the sub permissions? then they know what their options are?
                $(`#permission-${element}`).show(200);
            } else {
                $(`#permission-${element}`).hide(200);
            }
        });
    }

    $('form').submit(() => {
        $(':disabled').each(function() {
            $(this).removeAttr('disabled');
        });
    });

    @isset($role)
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    @endisset
</script>
@stop
