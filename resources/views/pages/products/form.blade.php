@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">{{ is_null($product) ? 'Create' : 'Edit' }} Product</h2>
@if(!is_null($product)) <h4 class="subtitle"><strong>Product:</strong> {{ $product->name }}</h4> @endif
<style>
    select:required:invalid {
        color: gray;
    }

    option[value=""][disabled] {
        display: none;
    }

    option {
        color: black;
    }
</style>
<div class="columns box">
    <div class="column is-1"></div>

    <div class="column is-5">
        @include('includes.messages')
        <form action="{{ is_null($product) ? route('products_new_form') : route('products_edit_form') }}" id="product_form" method="POST"
            class="form-horizontal">
            @csrf
            <input type="hidden" name="product_id" id="product_id" value="{{ $product->id ?? null }}">

            <div class="field">
                <label class="label">Name<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="name" class="input" placeholder="Name" required value="{{ $product->name ?? old('name') }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input" required value="{{ isset($product->price) ? number_format($product->price, 2) : number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        PST
                        <input type="checkbox" class="js-switch" name="pst" {{ (isset($product->pst) && $product->pst) || old('pst') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <label class="label">Category<sup style="color: red">*</sup></label>
                <div class="control">
                    <div class="select">
                        <select name="category_id" required>
                            {!! !isset($product->category) ? "<option value=\"\" disabled selected>Select Category...</option>" : '' !!}
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ (!is_null($product) && $product->category_id === $category->id) || old('category') === $category->id  ? 'selected' : '' }}>
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
                    <input type="checkbox" class="js-switch" name="unlimited_stock" {{ (isset($product->unlimited_stock) && $product->unlimited_stock) || old('unlimited_stock') ? 'checked' : '' }}>
                </label>
            </div>
        </div>

        <div id="stock_attr" style="display: none;">

            <div class="field">
                <div class="control">
                    <label class="checkbox label">
                        Stock Override
                        <input type="checkbox" class="js-switch" name="stock_override" {{ (isset($product->stock_override) && $product->stock_override) || old('stock_override') ? 'checked' : '' }}>
                    </label>
                </div>
            </div>

            <div class="field">
                <label class="label">Stock</label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="number" step="1" name="stock" class="input" placeholder="Stock" value="{{ $product->stock ?? old('stock') }}">
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

        </form>
    </div>
    <div class="column is-2">
        <form>
            <div class="control">
                <button class="button is-success" type="submit" form="product_form">
                    <span class="icon is-small">
                        <i class="fas fa-check"></i>
                    </span>
                    <span>Submit</span>
                </button>
            </div>
        </form>
        <br>
        @if(!is_null($product))
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
        @endif
    </div>
</div>

@if(!is_null($product))
    <div class="modal">
        <div class="modal-background" onclick="closeModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirmation</p>
            </header>
            <section class="modal-card-body">
                <p>Are you sure you want to delete the product {{ $product->name }}?</p>
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

<script type="text/javascript">

    const switches = document.getElementsByClassName("js-switch");
    for (let i = 0; i < switches.length; i++) { new Switchery(switches.item(i), {color: '#48C774', secondaryColor: '#F56D71'}) }

    $(document).ready(function() {
        updateUnlimitedAttr($('input[type=checkbox][name=unlimited_stock]').prop('checked'));
    });

    $('input[type=checkbox][name=unlimited_stock]').change(function() {
        updateUnlimitedAttr($(this).prop('checked'))
    });

    function updateUnlimitedAttr(checked) {
        let div = $('#stock_attr');
        if (checked) div.fadeOut(200);
        else div.fadeIn(200);
    }

    @if(!is_null($product))
        const modal = document.querySelector('.modal');

        function openModal() {
            modal.classList.add('is-active');
        }

        function closeModal() {
            modal.classList.remove('is-active');
        }

        function deleteData() {
            const id = document.getElementById('product_id').value;
            let url = '{{ route("products_delete", ":id") }}';
            url = url.replace(':id', id);
            $("#deleteForm").attr('action', url);
            $("#deleteForm").submit();
        }
    @endif
</script>
@endsection
