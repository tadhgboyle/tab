@extends('layouts.default')
@section('content')
<h2>Edit User</h2>
<p>Editing: {{ DB::table('users')->where('id', request()->route('id'))->pluck('full_name')->first() }}</p>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/edit/{{ request()->route('id') }}/commit" id="edit_user" method="POST">
            @csrf
            <?php

            use App\User;
            use Illuminate\Support\Facades\DB;
            use App\Http\Controllers\SettingsController;

            $user_info = User::select('full_name', 'username', 'balance', 'role', 'password')->where('id', '=', request()->route('id'))->get();
            if (empty($user_info)) {
                return redirect('/users');
            }
            ?>
            <input type="hidden" name="id" value="{{ request()->route('id') }}">
            Full Name<input type="text" name="full_name" class="form-control" placeholder="Full Name" value="{{ $user_info['0']['full_name'] }}">
            Username<input type="text" name="username" class="form-control" placeholder="Username (Optional)" value="{{ $user_info['0']['username'] }}">
            Balance<input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance" value="{{ number_format($user_info['0']['balance'], 2) }}">

            <input type="radio" name="role" value="camper" @if($user_info['0']['role']=="camper" ) checked @endif>
            <label for="camper">Camper</label><br>
            <input type="radio" name="role" value="cashier" @if($user_info['0']['role']=="cashier" ) checked @endif>
            <label for="cashier">Cashier</label><br>
            <input type="radio" name="role" value="administrator" @if($user_info['0']['role']=="administrator" ) checked @endif>
            <label for="administrator">Administrator</label>
            <input type="password" name="password" class="form-control" placeholder="Password">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
    </div>
    <div class="col-md-4">
        @include('includes.messages')
        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
        @foreach(SettingsController::getCategories() as $category)
        {{ ucfirst($category->value) }} Limit/day
        <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="form-control" placeholder="Limit" value="{{ DB::table('user_limits')->where([['user_id', request()->route('id')], ['category', $category->value]])->pluck('limit_per_day')->first() }}">
        @endforeach
    </div>
    </form>
    <div class="col-md-2">
        <form>
            <button type="submit" form="edit_user">Edit User</button>
        </form>
        <br>
        <form>
            <button type="submit" formaction="/users/delete/{{ request()->route('id') }}">Delete User</button>
        </form>
    </div>
</div>
@endsection