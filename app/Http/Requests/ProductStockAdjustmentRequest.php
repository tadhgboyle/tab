<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\FormRequest as FormRequestContract;

class ProductStockAdjustmentRequest extends FormRequest implements FormRequestContract
{
    public function rules(): array
    {
        return [
            'adjust_stock' => 'numeric',
            'adjust_box' => 'numeric',
        ];
    }
}
