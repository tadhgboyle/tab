@extends('layouts.default')
@section('content')
<h2>Edit User</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="panel-body col-md-4">
        <form action="/users/edit/{{ request()->route('id') }}/commit" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <?php

            use App\User;

            $array = User::select('full_name', 'username', 'balance', 'role')->where('id', '=', request()->route('id'))->get();
            if (empty($array)) {
                return redirect('/users');
            }
            ?>
            <div class="form-group">
                <input type="hidden" name="id" value="{{ request()->route('id') }}">
                Full Name<input type="text" name="full_name" class="form-control" placeholder="Full Name" value="{{ $array['0']['full_name'] }}">
                Username<input type="text" name="username" class="form-control" placeholder="Username (Optional)" value="{{ $array['0']['username'] }}">
                Balance<input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance" value="{{ $array['0']['balance'] }}">

                <input type="radio" name="role" value="camper" @if($array['0']['role']=="camper" ) checked @endif>
                <label for="camper">Camper</label><br>
                <input type="radio" name="role" value="cashier" @if($array['0']['role']=="cashier" ) checked @endif>
                <label for="cashier">Cashier</label><br>
                <input type="radio" name="role" value="administrator" @if($array['0']['role']=="administrator" ) checked @endif>
                <label for="administrator">Administrator</label>

                <button type="submit">Edit User</button>
            </div>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection