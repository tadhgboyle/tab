@php
use App\Roles;
$role = Roles::find(request()->route('id'));
if (!is_null($role) && !Roles::canInteract(Auth::user()->role, request()->route('id'))) return redirect('settings')->with('error', 'You cannot interact with that group.')->send();
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($role) ? 'Create' : 'Edit' }} Role</h2>
<div class="columns">
    <div class="column box">
        @include('includes.messages')
        <form action="{{ route('settings_roles_new_form') }}" method="POST">
            @csrf
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
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
            <div class="control">
                <button class="button is-success" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Save</span>
                </button>
                <a class="button is-outlined" href="/settings">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column"></div>
    <div class="column box is-6">
        <h4 class="title has-text-weight-bold is-4">Permissions</h4>
    </div>
</div>
@stop