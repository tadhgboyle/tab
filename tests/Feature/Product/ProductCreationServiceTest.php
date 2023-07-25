<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use Cknow\Money\Money;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use App\Services\Products\ProductCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanCreateProductNormally(): void
    {
        $category = Category::factory()->create();
        $productService = new ProductCreationService(new ProductRequest([
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => $category->id,
            'box_size' => 22,
            'stock' => 10,
            'pst' => true,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreationService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertEquals(Money::parse(10_50), $product->price);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame(10, $product->stock);
        $this->assertSame(22, $product->box_size);
        $this->assertFalse($product->unlimited_stock);
        $this->assertFalse($product->stock_override);
        $this->assertTrue($product->pst);
    }

    public function testCanCreateProductWithoutPst(): void
    {
        $category = Category::factory()->create();
        $productService = new ProductCreationService(new ProductRequest([
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => $category->id,
            'box_size' => 22,
            'stock' => 10,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreationService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertEquals(Money::parse(10_50), $product->price);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame(10, $product->stock);
        $this->assertSame(22, $product->box_size);
        $this->assertFalse($product->unlimited_stock);
        $this->assertFalse($product->stock_override);
        $this->assertFalse($product->pst);
    }

    public function testCanCreateProductWithNullStock(): void
    {
        $productService = new ProductCreationService(new ProductRequest([
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => Category::factory()->create()->id,
            'stock' => null,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreationService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertTrue($product->unlimited_stock);
        $this->assertSame(0, $product->stock);
    }

    public function testCanCreateProductWithoutBoxSize(): void
    {
        $productService = new ProductCreationService(new ProductRequest([
            'name' => 'Test Product',
            'price' => 10_50,
            'category_id' => Category::factory()->create()->id,
            'restore_stock_on_return' => true,
        ]));

        $this->assertSame(ProductCreationService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame(-1, $product->box_size);
    }
}
