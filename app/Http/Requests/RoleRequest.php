<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class RoleRequest extends FormRequest
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
                'min:2',
                ValidationRule::unique('roles')->ignore($this->get('role_id')),
            ],
            'order' => [
                'required', // TODO: make this optional, if not provided, then assign lowest by default
                'numeric',
                'gt:0',
                ValidationRule::unique('roles')->ignore($this->get('role_id')),
            ],
        ];
    }
}
