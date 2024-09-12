<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class FamilyRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                ValidationRule::unique('families')->ignore($this->get('family_id')),
            ],
        ];
    }
}
