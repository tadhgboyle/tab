<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use App\Http\Requests\FormRequest as FormRequestContract;

class GiftCardRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                ValidationRule::unique('gift_cards', 'code')->ignore($this->get('gift_card_id')),
            ],
            'balance' => [
                'required',
                'numeric',
                'min:0',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:today',
            ],
        ];
    }
}
