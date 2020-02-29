@extends('layouts.default')
@section('content')
<h2>Login</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @if (\Session::has('error'))
        <div class="alert alert-danger">
            <p>{!! \Session::get('error') !!}</p>
        </div>
        @endif
        <form action="/login/auth" method="post">
            {{ csrf_field() }}
            <div class="form-group">
                Username<input type="text" name="username" class="form-control" value="" placeholder="Username">
                Password<input type="password" name="password" class="form-control" placeholder="Password">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@stop