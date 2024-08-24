<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class ProductVariantRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', Rule::unique('product_variants')->ignore($this->get('product_variant_id'))],
            'price' => ['required', 'numeric'],
            'stock' => ['required', 'integer', 'min:0'],
            // options validation:
            // - at least one option selected
            // - all selected options are valid for the product
            // - the option combination is unique for the products variants
            'option_values' => ['required', 'array'],
            'option_values.*' => [
                'required',
                // TODO: Fix this validation rule
                // Rule::exists('product_variant_option_values', 'id')
                //     ->whereIn('product_variant_option_id', Product::find($this->get('product_id'))?->variantOptions->pluck('id'))
                // TODO: Make sure they selected a value for all options
            ],
        ];
    }
}
