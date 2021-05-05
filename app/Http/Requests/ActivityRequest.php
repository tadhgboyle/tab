<?php

namespace App\Http\Requests;

use App\Helpers\CategoryHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;

class ActivityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'min:3',
                'max:255',
                ValidationRule::unique('activities')->ignore($this->get('activity_id')),
            ],
            'location' => [
                'min:3',
                'max:36',
                'nullable',
            ],
            'description' => [
                'min:3',
                'max:255',
                'nullable',
            ],
            'slots' => [
                'min:1',
                'numeric',
                'nullable',
                ValidationRule::requiredIf(!$this->has('unlimited_slots')),
            ],
            'price' => [
                'required',
                'numeric',
            ],
            'start' => [
                'required',
            ],
            'end' => [
                'required',
            ],
            'category_id' => [
                'required',
                'integer',
                ValidationRule::in(CategoryHelper::getInstance()->getActivityCategories()->pluck('id')),
            ],
        ];
    }
}
