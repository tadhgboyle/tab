
@extends('layouts.default')
@section('content')
@php
use App\Casts\CategoryType
@endphp
<h2 class="title has-text-weight-bold">{{ is_null($category) ? 'Create' : 'Edit' }} Category</h2>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')
        <form action="{{ is_null($category) ? route('settings_categories_new_form') : route('settings_categories_edit_form') }}" method="POST">
            @csrf
            <input type="hidden" name="category_id" id="category_id" value="{{ $category->id ?? null }}">
            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Category Name" value="{{ $category->name ?? old('name') }}">
                </div>
            </div>
            <div class="field">
                <label class="label">Type<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select" id="type">
                        <select name="type" class="input" required>
                            {!! is_null($category) ? "<option value=\"\" disabled selected>Select Type...</option>" : '' !!}
                            <option value="1" {{ (!is_null($category) && $category->type->id === CategoryType::TYPE_PRODUCTS_ACTIVITIES) || old('type') === CategoryType::TYPE_PRODUCTS_ACTIVITIES ? "selected" : "" }}>
                                Products + Activities
                            </option>
                            <option value="2" {{ (!is_null($category) && $category->type->id === CategoryType::TYPE_PRODUCTS) || old('type') === CategoryType::TYPE_PRODUCTS ? "selected" : "" }}>
                                Products
                            </option>
                            <option value="3" {{ (!is_null($category) && $category->type->id === CategoryType::TYPE_ACTIVITIES) || old('type') === CategoryType::TYPE_ACTIVITIES ? "selected" : "" }}>
                                Activities
                            </option>
                        </select>
                    </div>
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
                @if(!is_null($category))
                <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openModal();">
                    <span>Delete</span>
                    <span class="icon is-small">
                        <i class="fas fa-times"></i>
                    </span>
                </button>
                @endif
            </div>
        </form>
    </div>
    <div class="column">
    </div>
</div>

@if(!is_null($category))
<div class="modal">
    <div class="modal-background" onclick="closeModal();"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Confirmation</p>
        </header>
        <section class="modal-card-body">
            <p>Are you sure you want to delete the category <strong>{{ $category->name }}</strong>?</p>
            <form action="" id="deleteForm" method="GET">
                @csrf
            </form>
        </section>
        <footer class="modal-card-foot">
            <button class="button is-success" type="submit" onclick="deleteData();">Confirm</button>
            <button class="button" onclick="closeModal();">Cancel</button>
        </footer>
    </div>
</div>
@endif

<script>
    @if(!is_null($category))
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }

        function deleteData() {
            let url = '{{ route("settings_categories_delete", ":id") }}';
            url = url.replace(':id', {{ $category->id }});
            $("#deleteForm").attr('action', url);
            $("#deleteForm").submit();
        }
    @endif
</script>
@stop
