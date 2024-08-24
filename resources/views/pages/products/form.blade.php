@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($product) ? 'Edit' : 'Create' }} Product</h2>
@if(isset($product)) <h4 class="subtitle"><strong>Product:</strong> {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_VIEW)<a href="{{ route('products_view', $product->id) }}">(View)</a>@endpermission</h4> @endif
<form action="{{ isset($product) ? route('products_update', $product->id) : route('products_store') }}" id="product_form" method="POST" class="form-horizontal">
    @csrf

    @isset($product)
        @method('PUT')
        <input type="hidden" name="product_id" value="{{ $product->id }}">
    @endisset

    <div class="columns box">
        <div class="column is-1"></div>

        <div class="column is-5">
            @include('includes.messages')

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Name" required value="{{ $product->name ?? old('name') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">SKU</label>
                <div class="control">
                    <input type="text" name="sku" class="input" placeholder="SKU" value="{{ $product->sku ?? old('sku') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input money-input" required value="{{ (isset($product) ? $product->price->formatForInput() : null) ?? number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        PST
                        <input type="checkbox" name="pst" {{ (isset($product->pst) && $product->pst) || old('pst') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <label class="label">Category<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select is-fullwidth">
                        <select name="category_id" required>
                            {!! !isset($product) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (isset($product) && $product->category_id === $category->id) || old('category') === $category->id  ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="column is-4">
            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Unlimited Stock
                        <input type="checkbox" name="unlimited_stock" {{ (isset($product->unlimited_stock) && $product->unlimited_stock) || old('unlimited_stock') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Stock Override
                        <input type="checkbox" name="stock_override" {{ (isset($product->stock_override) && $product->stock_override) || old('stock_override') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Restore stock on return
                        <input type="checkbox" name="restore_stock_on_return" {{ (isset($product->restore_stock_on_return) && $product->restore_stock_on_return) || old('restore_stock_on_return') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <label class="label">Stock<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1" name="stock" required class="input" placeholder="Stock" value="{{ $product->stock ?? old('stock') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Box Size</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1" name="box_size" class="input" placeholder="Box Size" value="{{ $product->box_size ?? old('stock') }}">
                </div>
            </div>
        </div>
        <div class="column is-2">
            <form>
                <div class="control">
                    <button class="button is-light" type="submit" form="product_form">
                        💾 Submit
                    </button>
                </div>
            </form>
            <br>
            @isset($product)
                <div class="control">
                    <form>
                        <button class="button is-danger is-outlined" type="button" onclick="openModal();">
                            <span>Delete</span>
                            <span class="icon is-small">
                                <i class="fas fa-times"></i>
                            </span>
                        </button>
                    </form>
                </div>
            @endisset
        </div>
    </div>
</form>

@isset($product)
    <div class="modal">
        <div class="modal-background" onclick="closeModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirmation</p>
            </header>
            <section class="modal-card-body">
                <p>Are you sure you want to delete the product {{ $product->name }}?</p>
                <form action="{{ route('products_delete', $product->id) }}" id="deleteForm" method="POST">
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

    <script type="text/javascript">
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }
    </script>
@endisset
@endsection
