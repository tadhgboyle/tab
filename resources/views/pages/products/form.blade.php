@php

use App\Http\Controllers\SettingsController;
use App\Products;
$product = Products::find(request()->route('id'));
@endphp
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($product) ? 'Create' : 'Edit' }} Product</h2>
@if(!is_null($product)) <p><strong>Product:</strong> {{ $product->name }}</p> @endif
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
<div class="columns">
    <div class="column is-1"></div>

    <div class="column is-5">
        @include('includes.messages')
        <form action="/products/{{ is_null($product) ? 'new' : 'edit' }}" id="product_form" method="POST"
            class="form-horizontal">
            @csrf
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id ?? null }}">

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Name" value="{{ $product->name ?? old('name') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input" value="{{ isset($product->price) ? number_format($product->price, 2) : number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        PST
                        <input type="checkbox" name="pst" {{ (isset($product->pst) && $product->pst) || old('pst') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <label class="label">Category<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select">
                        <select name="category">
                            {{!! !isset($product->category) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}}
                            @foreach(SettingsController::getCategories() as $category)
                                <option value="{{ $category->value }}"
                                    {{ (!is_null($product) && $product->category == $category->value) || old('category') == $category->value  ? 'selected' : '' }}>
                                    {{ ucfirst($category->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
    </div>
    <div class="column is-4">

        <div class="field">
            <label class="label">Stock</label>
            <div class="control has-icons-left">
                <span class="icon is-small is-left">
                    <i class="fas fa-hashtag"></i>
                </span>
                <input type="number" step="1" name="stock" class="input unlimited_stock_attr" placeholder="Stock" value="{{ $product->stock ?? old('stock') }}" readonly>
            </div>
        </div>

        <div class="field">
            <label class="label">Box Size</label>
            <div class="control has-icons-left">
                <span class="icon is-small is-left">
                    <i class="fas fa-hashtag"></i>
                </span>
                <input type="number" step="1" name="box_size" class="input unlimited_stock_attr" placeholder="Box Size" value="{{ $product->box_size ?? old('stock') }}" readonly>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <label class="checkbox label">
                    Unlimited Stock
                    <input type="checkbox" name="unlimited_stock" {{ (isset($product->unlimited_stock) && $product->unlimited_stock) || old('unlimited_stock') ? 'checked' : '' }}>
                </label>
            </div>
        </div>

        <div class="field">
            <div class="control">
                <label class="checkbox label">
                    Stock Override
                    <input type="checkbox" name="stock_override" {{ (isset($product->stock_override) && $product->stock_override) || old('stock_override') ? 'checked' : '' }}>
                </label>
            </div>
        </div>

        </form>
    </div>
    <div class="column is-2">
        <form>
            <div class="control">
                <button class="button is-success" type="submit" form="product_form">
                    <span class="icon is-small">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>Submit</span>
                </button>
            </div>
        </form>
        <br>
        @if(!is_null($product))
            <div class="control">
                <form>
                    <a class="button is-danger is-outlined" href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal">
                        <span>Delete</span>
                        <span class="icon is-small">
                            <i class="fas fa-times"></i>
                        </span>
                    </a>
                </form>
            </div>
        @endif
    </div>
</div>
<div id="DeleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <form action="" id="deleteForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    @csrf
                    <p class="text-center">Are you sure you want to delete this product?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="button is-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="button is-danger" data-dismiss="modal"
                            onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        updateUnlimitedAttr($('input[type=checkbox][name=unlimited_stock]').prop('checked'));
    });
        
    $('input[type=checkbox][name=unlimited_stock]').change(function() {
        updateUnlimitedAttr($(this).prop('checked'))
    });
        
    function updateUnlimitedAttr(checked) {
        let fields = document.getElementsByClassName('unlimited_stock_attr');
        for (var i = 0; i < fields.length; i++) { 
            fields[i].readOnly = checked; 
        } 
    }

    function deleteData() {
        var id = document.getElementById('product_id').value;
        var url = '{{ route("delete_product", ":id") }}';
        url = url.replace(':id', id);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
</script>
@endsection