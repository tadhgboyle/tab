@extends('layouts.default', ['page' => 'families'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($family) ? 'Edit' : 'Create' }} Family</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        <form action="{{ isset($family) ? route('families_update', $family) : route('families_store') }}" method="POST">
            @csrf
            @isset($family)
                @method('PUT')
                <input type="hidden" name="family_id" value="{{ $family->id }}">
            @endisset

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Family Name" value="{{ $family->name ?? old('name') }}" required>
                </div>
            </div>

            <div class="control">
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
                <a class="button is-outlined" href="{{ route('families_list') }}">
                    <span>Cancel</span>
                </a>
            </div>
        </form>
    </div>
    <div class="column"></div>
</div>
@stop
