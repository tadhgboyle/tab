@extends('layouts.default')
@section('content')
<h2>Edit Category</h2>
<div class="row">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <form action="/settings/category/edit/{{ request()->route('id') }}/commit" method="POST">
            @csrf
            <?php

            use App\Settings;

            $category_info = Settings::select('value')->where('id', request()->route('id'))->get();
            if (empty($category_info)) {
                return redirect('/settings');
            }
            ?>
            Name<input type="text" name="name" class="form-control" placeholder="Category Name" value="{{ ucfirst($category_info['0']['value']) }}">
            <br>
            <button type="submit">Submit</button>
        </form>
    </div>
    <div class="col-md-4">
    </div>
</div>
@stop