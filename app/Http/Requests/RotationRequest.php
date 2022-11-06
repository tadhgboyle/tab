<?php

namespace App\Http\Requests;

use App\Helpers\RotationHelper;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class RotationRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
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
            ],
            'end' => [
                'required',
                'date',
                'after:start'
            ]
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     *
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (resolve(RotationHelper::class)->doesRotationOverlap(
                $this->get('start'),
                $this->get('end'),
                $this->get('rotation_id')
            )) {
                $validator->errors()->add('start_or_end', 'That Rotation would overlap an existing Rotation.');
            }
        });
    }
}
