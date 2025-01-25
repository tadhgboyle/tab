<?php

namespace Tests\Unit\Admin\Product;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Category;
use App\Enums\ProductStatus;
use App\Http\Requests\ProductRequest;
use App\Services\Products\ProductCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateProductNormally(): void
    {
        $category = Category::factory()->create();
        $productService = new ProductCreateService(new ProductRequest([
            'name' => 'Test Product',
            'sku' => 'SKU-123',
            'status' => ProductStatus::Active,
            'price' => 10_50,
            'category_id' => $category->id,
            'stock' => 10,
            'pst' => true,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreateService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame('SKU-123', $product->sku);
        $this->assertSame(ProductStatus::Active, $product->status);
        $this->assertEquals(Money::parse(10_50), $product->price);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame(10, $product->stock);
        $this->assertFalse($product->unlimited_stock);
        $this->assertFalse($product->stock_override);
        $this->assertTrue($product->pst);
    }

    public function testCanCreateProductWithoutPst(): void
    {
        $category = Category::factory()->create();
        $productService = new ProductCreateService(new ProductRequest([
            'name' => 'Test Product',
            'status' => ProductStatus::Active,
            'price' => 10_50,
            'category_id' => $category->id,
            'stock' => 10,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreateService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertEquals(Money::parse(10_50), $product->price);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame(10, $product->stock);
        $this->assertFalse($product->unlimited_stock);
        $this->assertFalse($product->stock_override);
        $this->assertFalse($product->pst);
    }

    public function testCanCreateProductWithNullStock(): void
    {
        $productService = new ProductCreateService(new ProductRequest([
            'name' => 'Test Product',
            'price' => 10_50,
            'status' => ProductStatus::Active,
            'category_id' => Category::factory()->create()->id,
            'stock' => null,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreateService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertTrue($product->unlimited_stock);
        $this->assertSame(0, $product->stock);
    }
}
