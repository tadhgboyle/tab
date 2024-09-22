<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Requests\FormRequest as FormRequestContract;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                Rule::unique('users')->ignore($this->get('user_id')),
            ],
            'username' => [
                'nullable',
                Rule::unique('users')->ignore($this->get('user_id')),
            ],
            'balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'rotations' => [
                'required',
                'array',
                'min:1',
            ],
            'rotations.*' => [
                'numeric',
                Rule::exists('rotations', 'id'),
            ],
            'role_id' => [
                'required',
                'numeric',
                Rule::in(auth()->user()->role->getRolesAvailable()->pluck('id')),
            ],
            'password' => [
                Rule::requiredIf($this->get('user_id') === null),
                'nullable',
                'confirmed',
                Password::min(8),
            ],
        ];
    }
}
