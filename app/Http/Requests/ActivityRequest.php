<?php

namespace App\Http\Requests;

use App\Helpers\CategoryHelper;
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
                'min:3',
                'max:255',
                ValidationRule::unique('activities')->ignore($this->get('activity_id')),
            ],
            'location' => [
                'nullable',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'min:3',
                'max:255',
            ],
            'slots' => [
                'nullable',
                'min:1',
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
}
