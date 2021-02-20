<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class ActivityRequest extends FormRequest
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
                'max:255',
                ValidationRule::unique('activities')->ignore($this->get('id'))
            ],
            'location' => [
                'min:3',
                'max:36',
                'nullable'
            ],
            'description' => [
                'min:3',
                'max:255',
                'nullable'
            ],
            'slots' => [
                'min:1',
                'numeric',
                'nullable',
                ValidationRule::requiredIf(!$this->has('unlimited_slots'))
            ],
            'price' => [
                'required',
                'numeric'
            ],
            'start' => [
                'required'
            ],
            'end' => [
                'required'
            ]
        ];
    }
}
