@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">Login</h2>
<div class="columns box">
    <div class="column"></div>
    <div class="column">
        @include('includes.messages')
        <form action="/login/auth" method="post">
            @csrf
            <div class="field">
                <label class="label">Username</label>
                <div class="control">
                    <input type="text" name="username" class="input" placeholder="Username" value="{{ old('username') }}">
                </div>
            </div>
            <div class="field">
                <label class="label">Password</label>
                <div class="control">
                    <input type="password" name="password" class="input" placeholder="Password">
                </div>
            </div>
            <div class="control">
                <button class="button is-primary" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-sign-in-alt"></i>
                    </span>
                    <span>Login</span>
                </button>
            </div>
        </form>
    </div>
    <div class="column"></div>
</div>
@stop