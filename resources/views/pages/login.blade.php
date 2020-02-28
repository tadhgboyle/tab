@extends('layouts.default')
@section('content')
<div>
    @include('common.errors')
    <h2>Login</h2>
    <p>Please enter your credentials.</p>
    <form action="/login/auth" method="post">
        {{ csrf_field() }}
        <div>
            <strong>Username</strong>
            <input type="text" name="username" value="" placeholder="Username">
            <span style="color:red;"></span>
        </div>
        <div>
            <strong>Password</strong>
            <input type="password" name="password" placeholder="Password">
            <span style="color:red;"></span>
        </div>
        <div class="form-group">
            <input type="submit" value="Login">
        </div>
    </form>
</div>
@stop