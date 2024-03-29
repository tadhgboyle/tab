<?php

namespace App\Http\Requests;

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
            'price' => [
                'required',
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
            'box_size' => [
                // TODO: gte -1 (use new Rule::when())
                ValidationRule::notIn([0]),
            ],
        ];
    }
}
