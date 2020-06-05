@extends('layouts.default')
@section('content')
<h2>Login</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @include('includes.messages')
        <form action="/login/auth" method="post">
            @csrf
            <div class="form-group">
                Username<input type="text" name="username" class="form-control" placeholder="Username">
                Password<input type="password" name="password" class="form-control" placeholder="Password">
                <br>
                <button type="submit" class="btn btn-xs btn-success">Login</button>
            </div>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@stop