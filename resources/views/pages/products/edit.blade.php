@extends('layouts.default')
@section('content')
<div class="panel-body">
    <form action="/products/edit/{{ request()->route('id') }}/commit" method="POST" class="form-horizontal">
        {{ csrf_field() }}
        <?php

        use App\Products;

        $array = Products::select('name', 'price')->where('id', '=', request()->route('id'))->get();
        if (empty($array)) {
            return redirect('/products');
        }
        ?>
        <div class="form-group">
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
            Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ $array['0']['name'] }}">
            Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ $array['0']['price'] }}">
            <button type="submit">Edit Product</button>
        </div>
    </form>
</div>
@endsection