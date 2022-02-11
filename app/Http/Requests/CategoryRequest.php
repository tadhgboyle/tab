<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class CategoryRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:36',
                ValidationRule::unique('categories')->ignore($this->get('category_id')),
            ],
            'type' => [
                'required',
                'integer',
                ValidationRule::in([1, 2, 3]),
            ],
        ];
    }
}
