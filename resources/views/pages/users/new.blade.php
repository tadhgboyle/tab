@extends('layouts.default')
@section('content')
<h2>Create a User</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="panel-body col-md-4">
        <form action="/users/new/commit" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <div class="form-group">
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

                <button type="submit">Create User</button>
            </div>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection