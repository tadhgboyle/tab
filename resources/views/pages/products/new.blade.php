@extends('layouts.default')
@section('content')
<h2>Create a Product</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="panel-body col-md-4">
        <form action="/products/new/commit" method="POST" class="form-horizontal">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ Auth::user()->id }}">
            Name<input type="text" name="name" class="form-control" placeholder="Name" value="{{ old('name') }}">
            Price<input type="number" step="0.01" name="price" class="form-control" placeholder="Price" value="{{ old('price') }}">
            <button type="submit">Create Product</button>
        </form>
    </div>
    <div class="col-md-4"></div>
</div>
@endsection