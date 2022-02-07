<?php

namespace App\Http\Requests;

use App\Helpers\RoleHelper;
use App\Helpers\RotationHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class UserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
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
                'min:0',
            ],
            'rotations' => [
                'required',
                'array',
                'min:1',
                'max:' . resolve(RotationHelper::class)->getRotations()->count(),
            ],
            'rotations.*' => [
                ValidationRule::exists('rotations', 'id'),
            ],
            'role_id' => [
                'required',
                ValidationRule::in(auth()->user()->role->getRolesAvailable()->pluck('id')),
            ],
            'password' => [
                'nullable',
                'confirmed',
                'min:6',
                /** @phpstan-ignore-next-line  */
                ValidationRule::requiredIf(!(request()->route()->getName() === 'users_edit_form') && resolve(RoleHelper::class)->isStaffRole($this->get('role_id'))),
            ],
        ];
    }
}
