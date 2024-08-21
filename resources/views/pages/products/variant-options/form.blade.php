@extends('layouts.default', ['page' => 'products'])
@section('content')
<h2 class="title has-text-weight-bold">{{ isset($productVariantOption) ? 'Edit' : 'Create' }} Product Variant Option</h2>
<h4 class="subtitle">
    <strong>Product:</strong> {{ $product->name }}
    <br>
    @if(isset($productVariantOption))<strong>Variant Option:</strong> {{ $productVariantOption->name }} @endif
</h4>
<div class="columns">
    <div class="column"></div>
    <div class="column box">
        @include('includes.messages')

        <form action="{{ isset($productVariantOption) ? route('products_variant-options_update', [$product, $productVariantOption]) : route('products_variant-options_store', $product) }}" method="POST" class="form-horizontal">
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

            <div class="field">
                <label class="label">Values<sup style="color: red">*</sup></label>
                <div class="control">
                    <input type="text" name="values" id="values" class="input" placeholder="value1,value2,value3" value="{{ (isset($productVariantOption) ? $productVariantOption->values->map->value->join(',') : null) ?? old('values') }}">
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
@stop
