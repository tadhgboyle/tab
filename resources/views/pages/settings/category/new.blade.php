@extends('layouts.default')
@section('content')
<h2>Create Category</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        @include('includes.messages')
        <form action="/settings/category/new" method="POST">
            @csrf
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
            <span>Name<sup style="color: red">*</sup></span>
            <input type="text" name="name" class="form-control" placeholder="Category Name" value={{ old('name') }}>
            <br>
            <button type="submit" class="btn btn-xs btn-success">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
    </div>
</div>
@stop