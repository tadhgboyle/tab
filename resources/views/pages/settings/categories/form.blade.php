@extends('layouts.default', ['page' => 'settings'])
@section('content')
@php
use App\Enums\CategoryType;
@endphp
<h2 class="title has-text-weight-bold">{{ isset($category) ? 'Edit' : 'Create' }} Category</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        <form action="{{ isset($category) ? route('settings_categories_update', $category->id) : route('settings_categories_store') }}" method="POST">
            @csrf
            @isset($category)
                @method('PUT')
                <input type="hidden" name="category_id" value="{{ $category->id }}">
            @endisset

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Category Name" value="{{ $category->name ?? old('name') }}" required>
                </div>
            </div>
            <div class="field">
                <label class="label">Type<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select is-fullwidth" id="type">
                        <select name="type" class="input" required>
                            {!! !isset($category) ? "<option value=\"\" disabled selected>Select Type...</option>" : '' !!}
                            <option value="1" {{ (isset($category) && $category->type === CategoryType::ProductsActivities) || old('type') === CategoryType::ProductsActivities->value ? "selected" : "" }}>
                                Products & Activities
                            </option>
                            <option value="2" {{ (isset($category) && $category->type === CategoryType::Products) || old('type') === CategoryType::Products->value ? "selected" : "" }}>
                                Products
                            </option>
                            <option value="3" {{ (isset($category) && $category->type === CategoryType::Activities) || old('type') === CategoryType::Activities->value ? "selected" : "" }}>
                                Activities
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="control">
                <button class="button is-light" type="submit">
                    💾 Save
                </button>
                <a class="button is-outlined" href="{{ route('settings') }}">
                    <span>Cancel</span>
                </a>
                @isset($category)
                <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                    <span>Delete</span>
                    <span class="icon is-small">
                        <i class="fas fa-times"></i>
                    </span>
                </button>
                @endisset
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>

@isset($category)
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the category <strong>{{ $category->name }}</strong>?</p>
            <form action="{{ route('settings_categories_delete', $category->id) }}" id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>

<script>
    const modal = document.querySelector('.modal');

    function openModal() {
        modal.classList.add('is-active');
    }

    function closeModal() {
        modal.classList.remove('is-active');
    }
</script>
@endisset
@stop
