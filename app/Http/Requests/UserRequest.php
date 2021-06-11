<?php

namespace App\Http\Requests;

use App\Helpers\RoleHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'full_name' => [
                'required',
                'min:4',
                ValidationRule::unique('users')->ignore($this->get('id')),
            ],
            'username' => [
                'nullable',
                'min:3',
                ValidationRule::unique('users')->ignore($this->get('id')),
            ],
            'balance' => [
                'nullable',
                'numeric',
            ],
            'role_id' => [
                'required',
                ValidationRule::in(auth()->user()->role->getRolesAvailable()->pluck('id')),
            ],
            'password' => [
                'nullable',
                'confirmed',
                'min:6',
                ValidationRule::requiredIf(in_array($this->get('role'), RoleHelper::getInstance()->getStaffRoles()->pluck('id')->toArray())),
            ],
        ];
    }
}
