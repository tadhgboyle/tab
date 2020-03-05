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

            $product_info = Products::select('name', 'price', 'pst')->where('id', '=', request()->route('id'))->get();
            if (empty($product_info)) {
                return redirect('/products');
            }
            ?>
            <input type="hidden" name="id" value="{{ request()->route('id') }}">
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
            Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ $product_info['0']['name'] }}">
            Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ number_format($product_info['0']['price'], 2) }}">
            PST<input type="checkbox" name="pst" {{ $product_info['0']['pst'] == 0 ? "" : "checked" }}>
            <br>
            <button type="submit">Edit Product</button>
        </form>
        <form>
            <button type="submit" formaction="/products/delete/{{ request()->route('id') }}">Delete Product</button>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection