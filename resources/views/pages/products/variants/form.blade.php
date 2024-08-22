@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($productVariant) ? 'Edit' : 'Create' }} Product Variant</h2>
<h4 class="subtitle">
    <strong>Product:</strong> {{ $product->name }}
    <br>
    @if(isset($productVariant))<strong>Variant:</strong> {{ $productVariant->sku }} @endif
</h4>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')

        <form action="{{ isset($productVariant) ? route('products_variants_update', [$product, $productVariant]) : route('products_variants_store', $product) }}" method="POST" class="form-horizontal">
            @csrf

            @isset($productVariant)
                @method('PUT')
                <input type="hidden" name="product_variant_id" id="product_variant_id" value="{{ $productVariant->id }}">
            @endif

            <label class="label">SKU<sup style="color: red">*</sup></label>
            <div class="field has-addons">
                <div class="control is-expanded">
                    <input type="text" name="sku" id="sku" class="input" placeholder="SKU" value="{{ $productVariant->sku ?? old('sku') }}" required>
                </div>
                <div class="control">
                    <button class="button is-info" id="generateSku">
                        Generate
                    </button>
                </div>
            </div>

            <div class="field">
                <label class="label">Price<sup style="color: red">*</sup></label>
                <div class="control has-icons-left">
                    <span class="icon is-small is-left">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <input type="number" step="0.01" name="price" class="input money-input" placeholder="Price" required value="{{ (isset($productVariant) ? $productVariant->price->formatForInput() : null) ?? number_format(old('price'), 2) }}">
                </div>
            </div>

            <div class="field">
                <label class="label">Stock<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="number" step="1" name="stock" class="input" placeholder="0" required value="{{ $productVariant->stock ?? old('stock', 0) }}">
                </div>
            </div>

            @foreach($product->variantOptions as $option)
                <div class="field">
                    <label class="label">{{ $option->name }}</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="option_values[{{ $option->id }}]">
                                <option value="" disabled {{ !isset($productVariant) || !$productVariant->optionValueAssignments->contains('product_variant_option_id', $option->id) && old("option_values.{$option->id}") === null ? "selected" : "" }}>Select {{ $option->name }}</option>

                                @foreach($option->values as $value)
                                    <option value="{{ $value->id }}" @if(isset($productVariant) && $productVariant->optionValueAssignments->contains('product_variant_option_value_id', $value->id) || old("option_values.{$option->id}") == $value->id) selected @endif>{{ $value->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            @endforeach

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
                @isset($productVariant)
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

<script>
    document.getElementById('generateSku').addEventListener('click', event => {
        event.preventDefault();

        const sku = document.getElementById('sku');
        const optionValues = document.querySelectorAll('select[name^="option_values["]');
        const skuParts = ["{{ $product->name }}"];

        optionValues.forEach((optionValue) => {
            if (optionValue.value) {
                skuParts.push(optionValue.options[optionValue.selectedIndex].text);
            }
        });

        sku.value = skuParts.join('-');
    }, false);
</script>
@stop
