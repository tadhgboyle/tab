<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class RoleRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
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
