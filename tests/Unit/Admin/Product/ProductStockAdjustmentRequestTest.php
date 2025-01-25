<?php

namespace Tests\Unit\Admin\Product;

use Tests\FormRequestTestCase;
use App\Http\Requests\ProductStockAdjustmentRequest;

class ProductStockAdjustmentRequestTest extends FormRequestTestCase
{
    public function testAdjustStockIsInteger(): void
    {
        $this->assertHasErrors('adjust_stock', new ProductStockAdjustmentRequest([
            'adjust_stock' => 'string',
        ]));

        $this->assertHasErrors('adjust_stock', new ProductStockAdjustmentRequest([
            'adjust_stock' => 4.20,
        ]));

        $this->assertNotHaveErrors('adjust_stock', new ProductStockAdjustmentRequest([
            'adjust_stock' => 1,
        ]));
    }
}
