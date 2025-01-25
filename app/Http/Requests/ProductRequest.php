<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use App\Helpers\CategoryHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class ProductRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                ValidationRule::unique('products')->ignore($this->get('product_id')),
            ],
            'sku' => [
                'nullable',
                ValidationRule::unique('products')->ignore($this->get('product_id')),
            ],
            'status' => [
                'required',
                ValidationRule::enum(ProductStatus::class),
            ],
            'price' => [
                'required',
                'numeric',
            ],
            'cost' => [
                'nullable',
                'numeric',
            ],
            'category_id' => [
                'required',
                'integer',
                ValidationRule::in(resolve(CategoryHelper::class)->getProductCategories()->pluck('id')),
            ],
            'stock' => [
                'required',
                'integer',
            ],
        ];
    }
}
