@extends('layouts.default')
@section('content')
<h2>Edit Product</h2>
@php
use App\Products;
use App\Http\Controllers\SettingsController;

$product = Products::find(request()->route('id'));
if ($product == null) return redirect('/products')->with('error', 'Invalid product.')->send();
@endphp
<p>Editing: {{ DB::table('products')->where('id', request()->route('id'))->pluck('name')->first() }}</p>
<div class="row">
    <div class="col-md-3"></div>
    <div class="col-md-4">
        <form action="/products/edit/{{ request()->route('id') }}/commit" id="edit_product" method="POST" class="form-horizontal">
            @csrf

            <input type="hidden" name="id" id="product_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
            <span>Name</span>
            <input type="text" name="name" class="form-control" placeholder="Name" value="{{ $product->name }}">
            <br>
            <span>Price</span>
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">$</div>
                </div>
                <input type="number" step="0.01" name="price" class="form-control" placeholder="Price"
                    value="{{ number_format($product->price, 2) }}">
            </div>
            <br>
            <span>PST</span>
            <input type="checkbox" name="pst" {{ $product->pst != 0 ? "checked" : "" }}>
            &nbsp;
            <span>Category</span>
            <select name="category">
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->value }}" {{ $product->category == $category->value ? "selected" : "" }}>
                    {{ ucfirst($category->value) }}</option>
                @endforeach
            </select>
            <br>
        </form>
    </div>
    <div class="col-md-4">
        <form>
            <button type="submit" form="edit_product" class="btn btn-xs btn-success">Submit</button>
        </form>
        <br>
        <form>
            <a href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal"
                class="btn btn-xs btn-danger">Delete</a>
        </form>
    </div>
    <div class="col-md-1"></div>
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