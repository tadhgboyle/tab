@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($productVariantOption) ? 'Edit' : 'Create' }} Product Variant Option</h2>
<h4 class="subtitle">
    <strong>Product:</strong> {{ $product->name }} @permission(\App\Helpers\Permission::PRODUCTS_VIEW)<a href="{{ route('products_view', $product->id) }}">(View)</a>@endpermission
    <br>
    @if(isset($productVariantOption))<strong>Variant Option:</strong> {{ $productVariantOption->name }} @endif
</h4>

<div class="columns">
    <div class="column">
        <div class="box">
            <form action="{{ isset($productVariantOption) ? route('products_variant-options_update', [$product, $productVariantOption]) : route('products_variant-options_store', $product) }}" id="createForm" method="POST" class="form-horizontal">
                @csrf

                @isset($productVariantOption)
                    @method('PUT')
                    <input type="hidden" name="product_variant_option_id" id="product_variant_option_id" value="{{ $productVariantOption->id }}">
                @endif

                <div class="field">
                    <label class="label">Name<sup style="color: red">*</sup></label>
                    <div class="control">
                        <input type="text" name="name" id="name" class="input" placeholder="Name" value="{{ $productVariantOption->name ?? old('name') }}">
                    </div>
                </div>

                <div class="control">
                    <button class="button is-success" type="submit">
                        <span class="icon is-small">
                            <i class="fas fa-save"></i>
                        </span>
                        <span>Save</span>
                    </button>
                    <a class="button is-outlined" href="{{ route('products_view', $product) }}">
                        <span>Cancel</span>
                    </a>
                    @isset($productVariantOption)
                        <button class="button is-danger is-outlined is-pulled-right" type="button" onclick="openDeleteModal();">
                            <span>Delete</span>
                            <span class="icon is-small">
                                <i class="fas fa-times"></i>
                            </span>
                        </button>
                    @endisset
                </div>
            </form>
        </div>
    </div>
    <div class="column">
        @isset($productVariantOption)
        <div class="box">
            <livewire:admin.products.variant-options.values-list :productVariantOption="$productVariantOption" />
        </div>
        @endisset
    </div>
</div>

@isset($productVariantOption)
    <div class="modal" id="delete-modal">
        <div class="modal-background" onclick="closeDeleteModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirmation</p>
            </header>
            <section class="modal-card-body">
                <p>Are you sure you want to delete the option <strong>{{ $productVariantOption->name }}</strong>?</p>
                <form action="{{ route('products_variant-options_delete', [$product, $productVariantOption]) }}" id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" form="deleteForm">Confirm</button>
                <button class="button" onclick="closeDeleteModal();">Cancel</button>
            </footer>
        </div>
    </div>

    <div class="modal" id="create-value-modal">
        <div class="modal-background" onclick="closeCreateValueModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Create Value</p>
            </header>
            <section class="modal-card-body">
                <form action="{{ route('products_variant-options_values_store', [$product, $productVariantOption]) }}" id="createValueForm" method="POST">
                    @csrf
                    @method('POST')
                    <div class="field">
                        <label class="label">Value<sup style="color: red">*</sup></label>
                        <div class="control">
                            <input type="text" name="value" id="value" class="input" placeholder="Value">
                        </div>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" form="createValueForm">Submit</button>
                <button class="button" onclick="closeCreateValueModal();">Cancel</button>
            </footer>
        </div>
    </div>

    <div class="modal" id="edit-value-modal">
        <div class="modal-background" onclick="closeEditValueModal();"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Edit Value</p>
            </header>
            <section class="modal-card-body">
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label class="label">Value<sup style="color: red">*</sup></label>
                        <div class="control">
                            <input type="text" name="value" id="edit-value" class="input" placeholder="Value">
                        </div>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" type="submit" form="editForm">Submit</button>
                <button class="button" onclick="closeEditValueModal();">Cancel</button>

                <form id="deleteVariantOptionValueForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="button is-danger is-outlined" type="submit">
                        <span>Delete</span>
                        <span class="icon is-small">
                            <i class="fas fa-times"></i>
                        </span>
                    </button>
                </form>
            </footer>
        </div>
    </div>

    <script>
        const deleteModal = document.getElementById('delete-modal');

        function openDeleteModal() {
            deleteModal.classList.add('is-active');
        }

        function closeDeleteModal() {
            deleteModal.classList.remove('is-active');
        }

        const createValueModal = document.getElementById('create-value-modal');

        function openCreateValueModal() {
            createValueModal.classList.add('is-active');
        }

        function closeCreateValueModal() {
            createValueModal.classList.remove('is-active');
        }

        const editValueModal = document.getElementById('edit-value-modal');

        function openEditValueModal(valueId, value) {
            const url = `/admin/products/{{ $product->id }}/variant-options/{{ $productVariantOption->id }}/values/${valueId}`;
            document.getElementById('editForm').action = url;
            document.getElementById('deleteVariantOptionValueForm').action = url;

            document.getElementById('edit-value').value = value;

            editValueModal.classList.add('is-active');
        }

        function closeEditValueModal() {
            editValueModal.classList.remove('is-active');
        }
    </script>
@endisset
@stop
