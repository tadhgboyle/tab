<?php

namespace Tests\Unit\Product;

use Tests\FormRequestTestCase;
use App\Http\Requests\ProductStockAdjustmentRequest;

class ProductStockAdjustmentRequestTest extends FormRequestTestCase
{
    public function testAdjustStockIsNumeric(): void
    {
        $this->assertHasErrors('adjust_stock', new ProductStockAdjustmentRequest([
            'adjust_stock' => 'string',
        ]));

        $this->assertNotHaveErrors('adjust_stock', new ProductStockAdjustmentRequest([
            'adjust_stock' => 1,
        ]));
    }

    public function testAdjustBoxIsNumeric(): void
    {
        $this->assertHasErrors('adjust_box', new ProductStockAdjustmentRequest([
            'adjust_box' => 'string',
        ]));

        $this->assertNotHaveErrors('adjust_box', new ProductStockAdjustmentRequest([
            'adjust_box' => 1,
        ]));
    }
}
