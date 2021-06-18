<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class RotationRequest extends FormRequest
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
                'min:2',
                ValidationRule::unique('rotations')->ignore($this->get('rotation_id')),
            ],
            'start' => [
                'required',
                'date',
                'after_or_equal:now'
            ],
            'end' => [
                'required',
                'date',
                'after:start'
            ]
        ];
    }
}
