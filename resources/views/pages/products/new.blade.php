@extends('layouts.default')
@section('content')
<h2>Create a Product</h2>
@php
use App\Http\Controllers\SettingsController;
@endphp
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
    <div class="col-md-4">
        @include('includes.messages')
        <form action="/products/new/commit" id="new_product" method="POST" class="form-horizontal">
            @csrf
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">

            <span>Name</span>
            <input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
            <br>
            <span>Price</span>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ old('price') }}">
            <br>

            <span>PST</span>
            <input type="checkbox" name="pst">
            <span>Category</span>
            <select name="category">
                <option value="" disabled selected>Select Category...</option>
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->value }}">{{ ucfirst($category->value) }}</option>
                @endforeach
            </select>
            <br>
        </form>
    </div>
    <div class="col-md-4">
        <button type="submit" form="new_product" class="btn btn-xs btn-success">Create Product</button>
    </div>
</div>
@endsection