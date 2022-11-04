<?php

namespace App\Http\Requests;

use App\Helpers\RoleHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class UserRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'min:4',
                ValidationRule::unique('users')->ignore($this->get('user_id')),
            ],
            'username' => [
                'nullable',
                'min:3',
                ValidationRule::unique('users')->ignore($this->get('user_id')),
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
                ValidationRule::exists('rotations', 'id'),
            ],
            'role_id' => [
                'required',
                'numeric',
                ValidationRule::in(auth()->user()->role->getRolesAvailable()->pluck('id')),
            ],
            'password' => [
                'nullable',
                'confirmed',
                'min:6',
                ValidationRule::requiredIf(function () {
                    /** @phpstan-ignore-next-line  */
                    return !(request()?->route()?->getName() === 'users_update')
                        && resolve(RoleHelper::class)->isStaffRole(
                            // jank stuff to make testing this request possible
                            is_numeric($this->get('role_id')) ? $this->get('role_id') : 0
                        );
                }),
            ],
        ];
    }
}
