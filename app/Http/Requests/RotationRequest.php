<?php

namespace App\Http\Requests;

use App\Helpers\RotationHelper;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class RotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
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
                'after_or_equal:now'
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
        $validator->after(function () {
            if (resolve(RotationHelper::class)->doesRotationOverlap($this->get('start'), $this->get('end'))) {
                return redirect()->back()->withInput()->with('error', 'That Rotation would overlap an existing Rotation.')->send();
            }
        });
    }
}
