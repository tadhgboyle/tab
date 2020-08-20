
@extends('layouts.default')
@section('content')
<h2 class="title has-text-weight-bold">Create Category</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')
        <form action="{{ route('settings_categories_new_form') }}" method="POST">
            @csrf
            <input type="hidden" name="editor_id" value="{{ Auth::user()->id }}">
            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Category Name" value="{{ old('name') }}">
                </div>
            </div>
            <div class="control">
                <button class="button is-success" type="submit">
                    <span class="icon is-small">
                        <i class="fas fa-save"></i>
                    </span>
                    <span>Save</span>
                </button>
                <a class="button is-outlined" href="{{ route('settings') }}">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>
@stop