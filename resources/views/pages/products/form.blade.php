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
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id ?? null }}">

            <span>Name<sup style="color: red">*</sup></span>
            <input type="text" name="name" class="form-control" placeholder="Name"
                value="{{ $product->name ?? old('name') }}">
            <br>
            <span>Price<sup style="color: red">*</sup></span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Price"
                    value="{{ isset($product->price) ? number_format($product->price, 2) : number_format(old('price'), 2) }}">
            </div>
            <br>

            <span>PST</span>
            <input type="checkbox" name="pst"
                {{ (isset($product->pst) && $product->pst) || old('pst') ? 'checked' : '' }}>
            &nbsp;
            <span>Category<sup style="color: red">*</sup></span>
            <select name="category">
                {{!! !isset($product->category) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}}
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->value }}"
                    {{ (!is_null($product) && $product->category == $category->value) || old('category') == $category->value  ? 'selected' : '' }}>
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
                value="{{ $product->stock ?? old('stock') }}">
        </div>
        <br>
        <span>Box Size</span>
        <div class="input-group">
            <div class="input-group-prepend">
                <div class="input-group-text">#</div>
            </div>
            <input type="number" step="1" name="box_size" class="form-control" placeholder="Box Size"
                value="{{ $product->box_size ?? old('stock') }}">
        </div>
        <br>

        <span>Unlimited Stock</span>
        <input type="checkbox" name="unlimited_stock"
            {{ (isset($product->unlimited_stock) && $product->unlimited_stock) || old('unlimited_stock') ? 'checked' : '' }}>
        &nbsp;
        <span>Stock Override</span>
        <input type="checkbox" name="stock_override"
            {{ (isset($product->stock_override) && $product->stock_override) || old('stock_override') ? 'checked' : '' }}>

        </form>
    </div>
    <div class="col-md-2">
        <form>
            <button type="submit" form="product_form"
                class="btn btn-xs btn-success">{{ is_null($product) ? 'Create' : 'Edit' }} Product</button>
        </form>
        <br>
        @if(!is_null($product))
        <form>
            <a href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal"
                class="btn btn-xs btn-danger">Delete</a>
        </form>
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
                        <button type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal"
                            onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
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