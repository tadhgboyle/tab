<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Products\ProductStockAdjustmentService;

class ProductStockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotAdjustStockWhenProductIsNotFound(): void
    {
        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => -1,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_INVALID_PRODUCT, $productService->getResult());
    }

    public function testCannotAdjustStockWithInvalidInput(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_stock' => 'invalid',
            'adjust_box' => 'invalid',
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_INVALID_INPUT, $productService->getResult());
    }

    public function testCannotAdjustStockWithoutInput(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_stock' => 0,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_NO_BOX_INPUT, $productService->getResult());
    }

    public function testCannotAdjustStockWithZeroInput(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_stock' => 0,
            'adjust_box' => 0,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_BOX_INPUT_ZERO, $productService->getResult());
    }

    public function testCanAdjustStockNormally(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'stock' => 0,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_stock' => 7,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_SUCCESS, $productService->getResult());

        $product->refresh();
        $this->assertsame(7, $product->stock);
        $this->assertTrue(session()->has('last_product'));
        $this->assertSame($product->id, session()->get('last_product')->id);
    }

    public function testCanAdjustStockWithBoxSize(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'stock' => 0,
            'box_size' => 5,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_box' => 5,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_SUCCESS, $productService->getResult());

        $product->refresh();
        $this->assertsame(25, $product->stock);
        $this->assertTrue(session()->has('last_product'));
        $this->assertSame($product->id, session()->get('last_product')->id);
    }

    public function testCanAdjustStockWithStockCountAndBoxSize(): void
    {
        $product = Product::factory()->create([
            'category_id' => Category::factory()->create()->id,
            'stock' => 0,
            'box_size' => 5,
        ]);

        $productService = (new ProductStockAdjustmentService(new Request([
            'product_id' => $product->id,
            'adjust_stock' => 1,
            'adjust_box' => 5,
        ])));

        $this->assertSame(ProductStockAdjustmentService::RESULT_SUCCESS, $productService->getResult());

        $product->refresh();
        $this->assertsame(26, $product->stock);
        $this->assertTrue(session()->has('last_product'));
        $this->assertSame($product->id, session()->get('last_product')->id);
    }
}
