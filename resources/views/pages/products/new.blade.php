@extends('layouts.default')
@section('content')
<h2>Create a Product</h2>
<style>
    select:required:invalid {
        color: gray;
    }

    option[value=""][disabled] {
        display: none;
    }

    option {
        color: black;
    }
</style>
<div class="row">
    <div class="col-md-4"></div>
    <div class="panel-body col-md-4">
        <form action="/products/new/commit" method="POST" class="form-horizontal">
            @csrf
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
            Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
            Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ old('price') }}">
            PST<input type="checkbox" name="pst">
            <br>
            <?php

            use App\Http\Controllers\SettingsController;
            ?>
            Category
            <select name="category">
                <option value="" disabled selected>Select Category...</option>
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->value }}">{{ ucfirst($category->value) }}</option>
                @endforeach
            </select>
            <br>
            <button type="submit">Create Product</button>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection