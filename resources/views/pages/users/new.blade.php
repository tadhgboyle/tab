@extends('layouts.default')
@section('content')
<h2>Create a User</h2>
<div class="row">
    <div class="col-md-2"></div>
    <div class="col-md-4">
        <form action="/users/new/commit" method="POST" id="create_user">
            @csrf
            Full Name<input type="text" name="full_name" class="form-control" placeholder="Full Name">
            Username<input type="text" name="username" class="form-control" placeholder="Username (Optional)">
            Balance<input type="number" step="0.01" name="balance" class="form-control" placeholder="Balance">

            <input type="radio" name="role" value="camper" checked>
            <label for="camper">Camper</label><br>
            <input type="radio" name="role" value="cashier">
            <label for="cashier">Cashier</label><br>
            <input type="radio" name="role" value="administrator">
            <label for="administrator">Administrator</label>

            <input type="password" name="password" class="form-control" placeholder="Password">
            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password">
    </div>
    <div class="col-md-4">
        @include('includes.messages')
        <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
        @php use App\Http\Controllers\SettingsController; @endphp
        @foreach(SettingsController::getCategories() as $category)
        <span>{{ ucfirst($category->value) }} Limit</span>
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">$</div>
            </div>
            <input type="number" step="0.01" name="limit[{{ $category->value }}]" class="form-control" placeholder="Limit">
        </div>
        <input type="radio" name="duration[{{ $category->value }}]" value="0">
        <label for="day">Day&nbsp;</label>
        <input type="radio" name="duration[{{ $category->value }}]" value="1">
        <label for="week">Week</label>
        <br>
        @endforeach
        </form>
    </div>
    <div class="col-md-2">
        <button type="submit" form="create_user" class="btn btn-xs btn-success">Create User</button>
    </div>
</div>
@endsection