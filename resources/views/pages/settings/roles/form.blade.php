@php
use App\Roles;
$role = Roles::find(request()->route('id'));
if (!is_null($role) && !Roles::canInteract(Auth::user()->role, request()->route('id'))) return redirect('settings')->with('error', 'You cannot interact with that group.')->send();
if (!is_null($role)) $role_permissions = json_decode($role->permissions);
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($role) ? 'Create' : 'Edit' }} Role</h2>
@if(!is_null($role)) <h4 class="subtitle"><strong>Role:</strong> {{ $role->name }}</h4>@endif
<div class="columns">
    <div class="column">
        <div class="box">
            @include('includes.messages')
            <form action="{{ is_null($role) ? route('settings_roles_new') : route('settings_roles_edit_form') }}" method="POST">
                @csrf
                <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
                <input type="hidden" name="id" value="{{ $role->id ?? '' }}">
                <div class="field">
                    <label class="label">Name<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="text" name="name" class="input" placeholder="Role Name" value="{{ $role->name ?? old('name') }}">
                    </div>
                </div>
                <div class="field">
                    <label class="label">Order<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="number" name="order" class="input" placeholder="Role Order" min="1" value="{{ $role->order ?? old('order') }}">
                    </div>
                </div>
                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            Staff
                            <input type="checkbox" name="staff" @if(isset($role->staff) && $role->staff) checked @endif>
                        </label>
                    </div>
                </div>
                <div class="field" id="superuser">
                    <div class="control">
                        <label class="checkbox">
                            Superuser
                            <input type="checkbox" name="superuser" @if(isset($role->superuser) && $role->superuser) checked @endif>
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
                </div>
            </form>
        </div>
    </div>
    <div class="column box is-8">
        <h4 class="title has-text-weight-bold is-4">Permissions</h4>
        <hr>
        <h4 class="subtitle"><strong>Cashier</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[cashier]" value="0" @if(!is_null($role) && (in_array('cashier', $role_permissions) || $role->superuser)) checked @endif>
                Create Orders
            </label>
        </div>
        <hr>
        <h4 class="subtitle"><strong>Users</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[users_list]" value="0" @if(!is_null($role) && (in_array('users_list', $role_permissions) || $role->superuser)) checked @endif>
                List Users
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[users_view]" value="0" @if(!is_null($role) && (in_array('users_view', $role_permissions) || $role->superuser)) checked @endif>
                View User Information
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[users_manage]" value="0" @if(!is_null($role) && (in_array('users_manage', $role_permissions) || $role->superuser)) checked @endif>
                Manage Users
            </label>
        </div>
        <hr>
        <h4 class="subtitle"><strong>Products</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[products_list]" value="0" @if(!is_null($role) && (in_array('products_list', $role_permissions) || $role->superuser)) checked @endif>
                List Products
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[products_manage]" value="0" @if(!is_null($role) && (in_array('products_manage', $role_permissions) || $role->superuser)) checked @endif>
                Manage Products
            </label>
        </div>
        <hr>
        <h4 class="subtitle"><strong>Orders</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[orders_list]" value="0" @if(!is_null($role) && (in_array('orders_list', $role_permissions) || $role->superuser)) checked @endif>
                List Orders
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[orders_view]" value="0" @if(!is_null($role) && (in_array('orders_view', $role_permissions) || $role->superuser)) checked @endif>
                View Order Information
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[orders_return]" value="0" @if(!is_null($role) && (in_array('orders_return', $role_permissions) || $role->superuser)) checked @endif>
                Return Orders
            </label>
        </div>
        <hr>
        <h4 class="subtitle"><strong>Statistics</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[statistics]" value="0" @if(!is_null($role) && (in_array('statistics', $role_permissions) || $role->superuser)) checked @endif>
                View Statistics
            </label>
        </div>
        <hr>
        <h4 class="subtitle"><strong>Settings</strong></h4>
        <div class="control">
            <label class="checkbox">
                <input type="checkbox" name="permission[settings_general]" value="0" @if(!is_null($role) && (in_array('settings_general', $role_permissions) || $role->superuser) || $role->superuser)) checked @endif>
                Manage General Settings
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[settings_categories_manage]" value="0" @if(!is_null($role) && (in_array('settings_categories_manage', $role_permissions) || $role->superuser)) checked @endif>
                Manage Categories
            </label>
            &nbsp;
            <label class="checkbox">
                <input type="checkbox" name="permission[settings_roles_manage]" value="0" @if(!is_null($role) && (in_array('settings_roles_manage', $role_permissions) || $role->superuser)) checked @endif>
                Manage Roles
            </label>
        </div>
    </div>
</div>
<script>
    // TODO: Hide superuser checkbox if staff is not selected
</script>
@stop