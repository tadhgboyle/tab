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
            Name<input type="text" name="name" class="form-control" placeholder="Category Name">
            <br>
            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
    </div>
</div>
@stop