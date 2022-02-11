<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class PayoutRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'identifier' => 'string|nullable',
            'amount' => 'required|numeric|gt:0',
        ];
    }
}
