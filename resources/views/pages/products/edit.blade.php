@extends('layouts.default')
@section('content')
<h2>Edit Product</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="panel-body col-md-4">
        <form action="/products/edit/{{ request()->route('id') }}/commit" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <?php

            use App\Products;

            $product_info = Products::select('name', 'price')->where('id', '=', request()->route('id'))->get();
            if (empty($product_info)) {
                return redirect('/products');
            }
            ?>
            <div class="form-group">
                <input type="hidden" name="id" value="{{ Auth::user()->id }}">
                Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ $product_info['0']['name'] }}">
                Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ $product_info['0']['price'] }}">
                <button type="submit">Edit Product</button>
            </div>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection