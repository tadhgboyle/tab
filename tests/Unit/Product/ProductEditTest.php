<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use App\Services\Products\ProductEditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductEditTest extends TestCase
{
    use RefreshDatabase;

    public function testCanEditProductNormally(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $productService = new ProductEditService(new ProductRequest([
            'product_id' => $product->id,
            'name' => 'Test Product',
            'sku' => 'SKU-123',
            'price' => 10_50,
            'category_id' => $category->id,
            'box_size' => 22,
            'stock' => 10,
            'pst' => true,
        ]), $product);

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame('SKU-123', $product->sku);
        $this->assertEquals(Money::parse(10_50), $product->price);
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame(10, $product->stock);
        $this->assertSame(22, $product->box_size);
        $this->assertFalse($product->unlimited_stock);
        $this->assertFalse($product->stock_override);
        $this->assertTrue($product->pst);
    }

    public function testCanCreateProductWithNullStock(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $category2 = Category::factory()->create();
        $productService = new ProductEditService(new ProductRequest([
            'product_id' => $product->id,
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => $category2->id,
        ]), $product);

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertTrue($product->unlimited_stock);
        $this->assertSame(0, $product->stock);
        $this->assertSame($product->category_id, $category2->id);
    }

    public function testCanCreateProductWithoutBoxSize(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $productService = new ProductEditService(new ProductRequest([
            'product_id' => $product->id,
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => $product->category_id,
        ]), $product);

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame(-1, $product->box_size);
    }
}
