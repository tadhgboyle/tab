@extends('layouts.default')
@section('content')
<h2><strong>Login</strong></h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @include('includes.messages')
        <form action="/login/auth" method="post">
            @csrf
            <span>Username<sup style="color: red">*</sup></span>
            <input type="text" name="username" class="form-control" placeholder="Username" value="{{ old('username') }}">
            <br>
            <span>Password<sup style="color: red">*</sup></span>
            <input type="password" name="password" class="form-control" placeholder="Password">
            <br>
            <button type="submit" class="btn btn-xs btn-success">Login</button>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@stop