<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Http\Requests\ProductRequest;
use App\Services\Products\ProductEditService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductEditTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotEditNonexistentProduct()
    {
        $productService = new ProductEditService(new ProductRequest([
            'product_id' => -1,
        ]));

        $this->assertSame(ProductEditService::RESULT_NOT_EXIST, $productService->getResult());
    }

    public function testCanEditProductNormally(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);
        $productService = new ProductEditService(new ProductRequest([
            'product_id' => $product->id,
            'name' => 'Test Product',
            'price' => 10.50,
            'category_id' => $category->id,
            'box_size' => 22,
            'stock' => 10,
            'pst' => true,
        ]));

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame('Test Product', $product->name);
        $this->assertSame(10.50, $product->price);
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
            'price' => 10.50,
            'category_id' => $category2->id,
        ]));

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
            'price' => 10.50,
            'category_id' => $product->category_id,
        ]));

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());

        $product = $productService->getProduct();
        $this->assertModelExists($product);
        $this->assertSame(-1, $product->box_size);
    }
}
