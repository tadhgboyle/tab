@extends('layouts.default')
@section('content')
@php
use App\Http\Controllers\SettingsController;
use App\Products;
$product = Products::find(request()->route('id'));
@endphp
<h2>{{ is_null($product) ? 'Create' : 'Edit' }} a Product</h2>
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
    <div class="col-md-2"></div>
    <div class="col-md-4">
        @include('includes.messages')
        <form action="/products/{{ is_null($product) ? 'new' : 'edit' }}/commit" id="product_form" method="POST"
            class="form-horizontal">
            @csrf
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">

            <span>Name</span>
            <input type="text" name="name" class="form-control" placeholder="Name"
                value="{{ $product->name ?? '' }}">
            <br>
            <span>Price</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Price"
                    value="{{ $product->price ?? '' }}">
            </div>
            <br>

            <span>PST</span>
            <input type="checkbox" name="pst" {{ isset($product->pst) && $product->pst == 1 ? 'checked' : '' }}>
            &nbsp;
            <span>Category</span>
            <select name="category">
                {{!! !isset($product->category) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}}
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->value }}"
                    {{ !is_null($product) && $product->category == $category->value ? 'selected' : '' }}>
                    {{ ucfirst($category->value) }}
                </option>
                @endforeach
            </select>
            <br>
    </div>
    <div class="col-md-4">

        <span>Stock</span>
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">#</div>
            </div>
            <input type="number" step="1" name="stock" class="form-control" placeholder="Stock"
                value="{{ $product->stock ?? '' }}">
        </div>
        <br>
        <span>Box Size</span>
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">#</div>
            </div>
            <input type="number" step="1" name="box_size" class="form-control" placeholder="Box Size"
                value="{{ $product->box_size ?? '' }}">
        </div>
        <br>

        <span>Unlimited Stock</span>
        <input type="checkbox" name="unlimited_stock"
            {{ isset($product->unlimited_stock) && $product->unlimited_stock == 1 ? 'checked' : '' }}>
        &nbsp;
        <span>Stock Override</span>
        <input type="checkbox" name="stock_override"
            {{ isset($product->stock_override) && $product->stock_override == 1 ? 'checked' : '' }}>

        </form>
    </div>
    <div class="col-md-2">
        <button type="submit" form="product_form"
            class="btn btn-xs btn-success">{{ is_null($product) ? 'Create' : 'Edit' }} Product</button>
    </div>
</div>
@endsection