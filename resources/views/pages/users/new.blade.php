@extends('layouts.default')
@section('content')
<h2>Create a User</h2>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/new/commit" method="POST" id="create_user">
            @csrf
            Full Name<input type="text" name="full_name" class="form-control" placeholder="Full Name" value="{{ old('full_name') }}">
            Username<input type="text" name="username" class="form-control" placeholder="Username (Optional)" value="{{ old('username') }}">
            Balance<input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance" value="{{ old('balance') }}">

            <input type="radio" name="role" value="camper" @if(old('role')=="camper" ) checked @endif>
            <label for="camper">Camper</label><br>
            <input type="radio" name="role" value="cashier" @if(old('role')=="cashier" ) checked @endif>
            <label for="cashier">Cashier</label><br>
            <input type="radio" name="role" value="administrator" @if(old('role')=="administrator" ) checked @endif>
            <label for="administrator">Administrator</label>

            <input type="password" name="password" class="form-control" placeholder="Password">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">

    </div>
    <div class="col-md-4">
        @if (\Session::has('error'))
        <div class="alert alert-danger alert-dismissable">
            <span>{!! \Session::get('error') !!}</span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>>
        </div>
        @endif
        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
        <?php

        use App\Http\Controllers\SettingsController;
        ?>
        @foreach(SettingsController::getCategories() as $category)
        {{ ucfirst($category->value) }} Limit/day
        <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="form-control" placeholder="Limit" value="{{ DB::table('user_limits')->where([['user_id', request()->route('id')], ['category', $category->value]])->pluck('limit_per_day')->first() }}">
        @endforeach
        </form>
    </div>
    <div class="col-md-2">
        <button type="submit" form="create_user">Create User</button>
    </div>
</div>
@endsection