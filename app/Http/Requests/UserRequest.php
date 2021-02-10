<?php

namespace App\Http\Requests;

use App\Http\Controllers\RoleController;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;

class UserRequest extends FormRequest
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
    public function rules(Request $request)
    {
        return [
            'full_name' => [
                'required',
                'min:4',
                ValidationRule::unique('users')
            ],
            'username' => [
                'required',
                'min:3',
                ValidationRule::unique('users')
            ],
            'balance' => [
                'nullable'
            ],
            'role' => [
                'required',
                ValidationRule::in(array_column(Auth::user()->role->getRolesAvailable(), 'id'))
            ],
            'password' => [
                'nullable',
                'confirmed',
                'min:6',
                ValidationRule::requiredIf(in_array($request->role, array_column(RoleController::getInstance()->getStaffRoles(), 'id'))),
            ]
        ];
    }
}
