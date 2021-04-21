<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class CategoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:36',
                ValidationRule::unique('categories')->ignore($this->get('category_id'))
            ],
            'type' => [
                'required',
                'integer',
                ValidationRule::in([1, 2, 3])
            ]
        ];
    }
}
