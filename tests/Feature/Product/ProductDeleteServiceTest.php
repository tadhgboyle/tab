<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Services\Products\ProductEditService;
use App\Services\Products\ProductDeleteService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductDeleteServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testCanDeleteProduct(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->state([
            'category_id' => $category->id,
        ])->create();

        $productService = new ProductDeleteService($product);

        $this->assertSame(ProductEditService::RESULT_SUCCESS, $productService->getResult());
        $this->assertSoftDeleted($product);
    }
}
