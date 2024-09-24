<?php

namespace App\Http\Requests;

use App\Enums\CategoryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class CategoryRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                ValidationRule::unique('categories')->ignore($this->get('category_id')),
            ],
            'type' => [
                'required',
                ValidationRule::enum(CategoryType::class),
            ],
        ];
    }
}
