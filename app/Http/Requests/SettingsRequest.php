<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class SettingsRequest extends FormRequest implements FormRequestContract
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'gst' => 'required|numeric',
            'pst' => 'required|numeric',
        ];
    }
}
