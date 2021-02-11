<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

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
                ValidationRule::unique('products')->ignore($this->get('id'))
            ],
            'price' => [
                'required',
                'numeric'
            ],
            'category' => [
                'required'
            ],
            'box_size' => [
                // TODO: gte -1
                ValidationRule::notIn(0)
            ]
        ];
    }
}
