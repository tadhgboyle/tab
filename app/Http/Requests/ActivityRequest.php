<?php

namespace App\Http\Requests;

use App\Helpers\CategoryHelper;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class ActivityRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                ValidationRule::unique('activities')->ignore($this->get('activity_id')),
            ],
            'location' => [
                'nullable',
            ],
            'description' => [
                'nullable',
            ],
            'slots' => [
                'nullable',
                'numeric',
                ValidationRule::requiredIf(!$this->has('unlimited_slots')),
            ],
            'price' => [
                'required',
                'numeric',
            ],
            'start' => [
                'required',
                'date',
                'after_or_equal:now',
            ],
            'end' => [
                'required',
                'date',
                'after:start',
            ],
            'category_id' => [
                'required',
                'integer',
                ValidationRule::in(resolve(CategoryHelper::class)->getActivityCategories()->pluck('id')),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->has('unlimited_slots') && $this->get('slots') < 1) {
                $validator->errors()->add('slots', 'Slots must be at least 1.');
            }
        });
    }
}
