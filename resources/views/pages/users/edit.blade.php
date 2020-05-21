@extends('layouts.default')
@section('content')
<h2>Edit User</h2>
@php
use App\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserLimitsController;

$user = User::find(request()->route('id'));
if ($user == null) return redirect('/users')->with('error', 'Invalid user.')->send();
@endphp
<p>User: {{ $user->full_name }} <a href="/users/info/{{ request()->route('id') }}">(Info)</a></p>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/edit/{{ request()->route('id') }}/commit" id="edit_user" method="POST">
            @csrf
            <input type="hidden" name="id" id="user_id" value="{{ request()->route('id') }}">

            <span>Full Name</span>
            <input type="text" name="full_name" class="form-control" placeholder="Full Name"
                value="{{ $user->full_name }}">
            <span>Username</span>
            <input type="text" name="username" class="form-control" placeholder="Username (Optional)"
                value="{{ $user->username }}">
            <span>Balance</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance"
                    value="{{ number_format($user->balance, 2) }}">
            </div>

            <input type="radio" name="role" value="camper" @if($user->role == "camper") checked @endif>
            <label for="camper">Camper</label><br>
            <input type="radio" name="role" value="cashier" @if($user->role == "cashier") checked @endif>
            <label for="cashier">Cashier</label><br>
            <input type="radio" name="role" value="administrator" @if($user->role == "administrator") checked @endif>
            <label for="administrator">Administrator</label>

            <!-- TODO: Make these only show when a staff role is selected above -->
            <input type="password" name="password" class="form-control" placeholder="Password">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
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
                    placeholder="Limit" value="{{ UserLimitsController::findLimit($user->id, $category->value) }}">
            </div>
            <input type="radio" name="duration[{{ $category->value }}]" value="0"
                @if(UserLimitsController::findDuration($user->id, $category->value) == "day") checked @endif>
            <label for="day">Day</label>&nbsp;
            <input type="radio" name="duration[{{ $category->value }}]" value="1"
                @if(UserLimitsController::findDuration($user->id, $category->value) == "week") checked @endif>
            <label for="week">Week</label>
            <br>
        @endforeach
    </div>
    </form>
    <div class="col-md-2">
        <form>
            <button type="submit" form="edit_user" class="btn btn-xs btn-success">Submit</button>
        </form>
        <br>
        <form>
            <a href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal"
                class="btn btn-xs btn-danger">Delete</a>
        </form>
    </div>
</div>
<div id="DeleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <form action="" id="deleteForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    {{ csrf_field() }}
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