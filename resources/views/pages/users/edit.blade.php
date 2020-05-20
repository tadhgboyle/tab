@extends('layouts.default')
@section('content')
<h2>Edit User</h2>
<p>User: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }} <a
        href="/users/info/{{ request()->route('id') }}">(Info)</a></p>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/edit/{{ request()->route('id') }}/commit" id="edit_user" method="POST">
            @csrf
            <?php

            use App\User;
            use Illuminate\Support\Facades\DB;
            use App\Http\Controllers\SettingsController;
            use App\Http\Controllers\UserLimitsController;

            $user_info = User::select('full_name', 'username', 'balance', 'role', 'password')->where('id', '=', request()->route('id'))->get();
            if (empty($user_info)) {
                return redirect('/users');
            }
            ?>
            <input type="hidden" name="id" id="user_id" value="{{ request()->route('id') }}">
            Full Name<input type="text" name="full_name" class="form-control" placeholder="Full Name"
                value="{{ $user_info['0']['full_name'] }}">
            Username<input type="text" name="username" class="form-control" placeholder="Username (Optional)"
                value="{{ $user_info['0']['username'] }}">
            Balance<div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance"
                    value="{{ number_format($user_info['0']['balance'], 2) }}">
            </div>
            <input type="radio" name="role" value="camper" @if($user_info['0']['role']=="camper" ) checked @endif>
            <label for="camper">Camper</label><br>
            <input type="radio" name="role" value="cashier" @if($user_info['0']['role']=="cashier" ) checked @endif>
            <label for="cashier">Cashier</label><br>
            <input type="radio" name="role" value="administrator" @if($user_info['0']['role']=="administrator" ) checked
                @endif>
            <label for="administrator">Administrator</label>
            <input type="password" name="password" class="form-control" placeholder="Password">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
    </div>
    <div class="col-md-4">
        @include('includes.messages')
        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
        @foreach(SettingsController::getCategories() as $category)
        {{ ucfirst($category->value) }} Limit
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">$</div>
            </div>
            <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="form-control"
                placeholder="Limit"
                value="{{ DB::table('user_limits')->where([['user_id', request()->route('id')], ['category', $category->value]])->pluck('limit_per')->first() }}">
        </div>
        <input type="radio" name="duration[{{ $category->value }}]" value="0"
            @if(UserLimitsController::findDuration(request()->route('id') ,$category->value) == "day") checked @endif>
        <label for="day">Day</label>&nbsp;
        <input type="radio" name="duration[{{ $category->value }}]" value="1"
            @if(UserLimitsController::findDuration(request()->route('id') ,$category->value) == "week") checked @endif>
        <label for="week">Week</label>
        <br>
        @endforeach
    </div>
    </form>
    <div class="col-md-2">
        <form>
            <button type="submit" form="edit_user">Submit</button>
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