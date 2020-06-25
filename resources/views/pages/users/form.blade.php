@extends('layouts.default')
@section('content')
@php
use App\Http\Controllers\UserLimitsController;
use App\Http\Controllers\SettingsController;
use App\User;
$user = User::find(request()->route('id'));
if (!is_null($user) && $user->deleted) return redirect('/users')->with('error', 'That user has been deleted.')->send();
@endphp
<h2>{{ is_null($user) ? 'Create' : 'Edit' }} a User</h2>
@if(!is_null($user)) <p>User: {{ $user->full_name }} <a href="/users/info/{{ $user->id }}">(Info)</a></p> @endif
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/{{ is_null($user) ? 'new' : 'edit' }}/commit" id="user_form" method="POST">
            @csrf
            <input type="hidden" name="id" id="user_id" value="{{ request()->route('id') }}">

            <!-- 
                Short hand ternary: 
                <A> ?? <B> 
                A will be shown if not null, but if it is null, B will be shown
            -->
            <span>Full Name<sup style="color: red">*</sup></span>
            <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                value="{{ $user->full_name ?? old('full_name') }}">

            <span>Username</span>
            <input type="text" name="username" class="form-control" placeholder="Username (Optional)"
                value="{{ $user->username ?? old('username') }}">

            <span>Balance</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance"
                    value="{{ isset($user->balance) ? number_format($user->balance, 2) : number_format(old('balance'), 2) }}">
            </div>

            <label for="camper">Camper</label>
            <input type="radio" name="role" value="camper" @if((isset($user->role) && $user->role ==
            "camper")||
            old('role') == "camper") checked @endif><br>
            <label for="cashier">Cashier</label>
            <input type="radio" name="role" value="cashier" @if((isset($user->role) && $user->role ==
            "cashier")||
            old('role') == "cashier") checked @endif><br>
            <label for="administrator">Administrator</label>
            <input type="radio" name="role" value="administrator" @if((isset($user->role) && $user->role ==
            "administrator") || old('role') == "administrator") checked @endif>

            <input type="password" name="password" class="form-control password_hidable" placeholder="Password"
                autocomplete="new-password" readonly>
            <input type="password" name="password_confirmation" class="form-control password_hidable"
                placeholder="Confirm Password" autocomplete="new-password" readonly>
    </div>

    <div class="col-md-4">
        @include('includes.messages')

        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">

        @foreach(SettingsController::getCategories() as $category)
        <span>{{ ucfirst($category->value) }} Limit</span>
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">$</div>
            </div>
            <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="form-control"
                placeholder="Limit"
                value="{{ isset($user->id) ? UserLimitsController::findLimit($user->id, $category->value) : '' }}">
        </div>
        <input type="radio" name="duration[{{ $category->value }}]" value="0" @if(isset($user->id) &&
        UserLimitsController::findDuration($user->id, $category->value) == "day") checked @endif>
        <label for="day">Day</label>&nbsp;
        <input type="radio" name="duration[{{ $category->value }}]" value="1" @if(isset($user->id) &&
        UserLimitsController::findDuration($user->id, $category->value) == "week") checked @endif>
        <label for="week">Week</label>
        <br>
        @endforeach
    </div>
    </form>
    <div class="col-md-2">
        <form>
            <button type="submit" form="user_form" class="btn btn-xs btn-success">Submit</button>
        </form>
        <br>
        @if(!is_null($user))
        <form>
            <a href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal"
                class="btn btn-xs btn-danger">Delete</a>
        </form>
        @endif
    </div>
</div>
<div id="DeleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <form action="" id="deleteForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    @csrf
                    <p class="text-center">Are you sure you want to delete this user?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal"
                            onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
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
        var url = '{{ route("delete_user", ":id") }}';
        url = url.replace(':id', id);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
</script>
@endsection