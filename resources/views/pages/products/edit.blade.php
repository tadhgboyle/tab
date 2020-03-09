@extends('layouts.default')
@section('content')
<h2>Edit Product</h2>
<p>Editing: {{ DB::table('products')->where('id', request()->route('id'))->pluck('name')->first() }}</p>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <form action="/products/edit/{{ request()->route('id') }}/commit" method="POST" class="form-horizontal">
            @csrf
            <?php

            use App\Products;

            $product_info = Products::select('name', 'category', 'price', 'pst')->where('id', '=', request()->route('id'))->get();
            if (empty($product_info)) {
                return redirect('/products');
            }
            ?>
            <input type="hidden" name="id" id="product_id" value="{{ request()->route('id') }}">
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
            Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ $product_info['0']['name'] }}">
            Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ number_format($product_info['0']['price'], 2) }}">
            PST<input type="checkbox" name="pst" {{ $product_info['0']['pst'] == 0 ? "" : "checked" }}>
            <br>
            <?php

            use App\Http\Controllers\SettingsController;
            ?>
            Category
            <select id="categories">
                @foreach(SettingsController::getCategories() as $category)
                <option value="{{ $category->id }}" {{ $product_info['0']['category'] == $category->value ? "selected" : "" }}>{{ ucfirst($category->value) }}</option>
                @endforeach
            </select>
            <br>
            <button type="submit">Edit Product</button>
        </form>
        <form>
            <a href="javascript:;" data-toggle="modal" onclick="deleteData()" data-target="#DeleteModal" class="btn btn-xs btn-danger">Delete</a>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
<div id="DeleteModal" class="modal fade" role="dialog">
    <div class="modal-dialog ">
        <!-- Modal content-->
        <form action="" id="deleteForm" method="get">
            <div class="modal-content">
                <div class="modal-body">
                    {{ csrf_field() }}
                    <p class="text-center">Are you sure you want to delete this product?</p>
                </div>
                <div class="modal-footer">
                    <center>
                        <button type="button" class="btn btn-success" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="" class="btn btn-danger" data-dismiss="modal" onclick="formSubmit()">Delete</button>
                    </center>
                </div>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    function deleteData() {
        var id = document.getElementById('product_id').value;
        console.log(id);
        var url = '{{ route("delete_product", ":id") }}';
        url = url.replace(':id', id);
        $("#deleteForm").attr('action', url);
    }

    function formSubmit() {
        $("#deleteForm").submit();
    }
</script>
@endsection