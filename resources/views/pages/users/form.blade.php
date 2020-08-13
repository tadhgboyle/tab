@php

use App\Http\Controllers\UserLimitsController;
use App\Http\Controllers\SettingsController;
use App\User;
use App\Roles;
$user = User::find(request()->route('id'));
if (!is_null($user) && $user->deleted) return redirect('/users')->with('error', 'That user has been deleted.')->send();
$users_view = Roles::hasPermission(Auth::user()->role, 'users_view');
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($user) ? 'Create' : 'Edit' }} User</h2>
@if(!is_null($user)) <h4 class="subtitle"><strong>User:</strong> {{ $user->full_name }} @if($users_view)<a href="/users/view/{{ $user->id }}">(View)</a>@endif</h4>@endif
<div class="columns box">
    <div class="column is-1"></div>

    <div class="column is-5">
        <form action="/users/{{ is_null($user) ? 'new' : 'edit' }}" id="user_form" method="POST">
            @csrf
            <input type="hidden" name="id" id="user_id" value="{{ request()->route('id') }}">

            <div class="field">
                <label class="label">Full Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="full_name" class="input" placeholder="Full Name" value="{{ $user->full_name ?? old('full_name') }}">
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
                    <input type="number" step="0.01" name="balance" class="input" value="{{ isset($user->balance) ? number_format($user->balance, 2) : number_format(old('balance'), 2) }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="label">Role<sup style="color: red">*</sup></label>
                        <label class="radio">
                        <input type="radio" name="role" value="camper" @if((isset($user->role) && $user->role ==  "camper") || old('role') == "camper") checked @endif>
                        Camper
                    </label>
                    <label class="radio">
                        <input type="radio" name="role" value="cashier" @if((isset($user->role) && $user->role == "cashier") || old('role') == "cashier") checked @endif>
                        Cashier
                    </label>
                    <label class="radio">
                        <input type="radio" name="role" value="administrator" @if((isset($user->role) && $user->role == "administrator") || old('role') == "administrator") checked @endif>
                        Administrator
                    </label>
                </div>
            </div>

            <label class="label">Password</label>
            <div class="field">
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password" class="input password_hidable" placeholder="Password" autocomplete="new-password" readonly>
                </div>
            </div>
            <div class="field">
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" name="password_confirmation" class="input password_hidable" placeholder="Confirm Password" autocomplete="new-password" readonly>
                </div>
            </div>
    </div>

    <div class="column is-4">
        @include('includes.messages')

        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">

        @foreach(SettingsController::getCategories() as $category)
            <div class="field">
                <label class="label">{{ ucfirst($category->value) }} Limit</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="input" placeholder="Limit" value="{{ isset($user->id) ? number_format(UserLimitsController::findLimit($user->id, $category->value), 2) : '' }}">
                </div>
                <div class="control">
                    <label class="radio">
                        <input type="radio" name="duration[{{ $category->value }}]" value="0" @if(isset($user->id) && UserLimitsController::findDuration($user->id, $category->value) == "day") checked @endif>
                        Day
                    </label>
                    <label class="radio">
                        <input type="radio" name="duration[{{ $category->value }}]" value="1" @if(isset($user->id) && UserLimitsController::findDuration($user->id, $category->value) == "week") checked @endif>
                        Week
                    </label>
                </div>
            </div>
        @endforeach
    </div>
    </form>
    <div class="column is-2">
        <form>
            <div class="control">
                <button class="button is-success" type="submit" form="user_form">
                    <span class="icon is-small">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>Submit</span>
                </button>
            </div>
        </form>
        <br>
        @if(!is_null($user))
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
        @endif
    </div>
</div>

@if(!is_null($user))
    <div class="modal">
        <div class="modal-background" onclick="closeModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirmation</p>
            </header>
            <section class="modal-card-body">
                <p>Are you sure you want to delete the user {{ $user->full_name }}?</p>
                <form action="" id="deleteForm" method="GET">
                    @csrf
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" onclick="deleteData();">Confirm</button>
                <button class="button" onclick="closeModal();">Cancel</button>
            </footer>
        </div>
    </div>
@endif

<script type="text/javascript">
    $(document).ready(function() {
        updatePassword($("input[name=role]:checked").val());
    });

    $('input[type=radio][name=role]').change(function() {
        updatePassword(this.value)
    });

    function updatePassword(role) {
        if (role !== undefined) {
            let fields = document.getElementsByClassName('password_hidable');
            for (var i = 0; i < fields.length; i++) { 
                fields[i].readOnly = role != 'camper' ? false : true; 
            }
        }
    }

    function deleteData() {
        var id = document.getElementById('user_id').value;
        var url = '{{ route("users_delete", ":id") }}';
        url = url.replace(':id', id);
        $("#deleteForm").attr('action', url);
        $("#deleteForm").submit();
    }

    const modal = document.querySelector('.modal');

    function openModal() {
        modal.classList.add('is-active');
    }

    function closeModal() {
        modal.classList.remove('is-active');
    }
</script>
@endsection