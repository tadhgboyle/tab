<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use App\Models\Product;
use App\Services\Products\ProductDeleteService;
use App\Services\Products\ProductEditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function testCannotDeleteNonexistentProduct(): void
    {
        $productService = new ProductDeleteService(-1);

        $this->assertSame(ProductEditService::RESULT_NOT_EXIST, $productService->getResult());
    }

    public function testCanDeleteProduct(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->state([
            'category_id' => $category->id,
        ])->create();

        $productService = new ProductDeleteService($product->id);

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());
        $this->assertSoftDeleted($product);
    }
}
