<?php

namespace Tests\Unit\Admin\Product;

use App\Models\Product;
use App\Models\Category;
use App\Enums\ProductStatus;
use Tests\FormRequestTestCase;
use App\Http\Requests\ProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndUnique(): void
    {
        $this->assertHasErrors('name', new ProductRequest([
            'name' => null
        ]));

        $this->assertNotHaveErrors('name', new ProductRequest([
            'name' => 'skittles'
        ]));

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Product',
            'category_id' => $category->id,
        ]);

        $this->assertHasErrors('name', new ProductRequest([
            'name' => $product->name,
        ]));

        $this->assertNotHaveErrors('name', new ProductRequest([
            'product_id' => $product->id,
            'name' => $product->name,
        ]));

        $this->assertNotHaveErrors('name', new ProductRequest([
            'name' => 'valid name',
        ]));
    }

    public function testSkuIsOptionalAndUnique(): void
    {
        $this->assertNotHaveErrors('sku', new ProductRequest([
            'sku' => null
        ]));

        $this->assertNotHaveErrors('sku', new ProductRequest([
            'sku' => 'HOODIE-123'
        ]));

        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'name' => 'Product',
            'sku' => 'SKU-123',
            'category_id' => $category->id,
        ]);

        $this->assertHasErrors('sku', new ProductRequest([
            'sku' => $product->sku,
        ]));

        $this->assertNotHaveErrors('sku', new ProductRequest([
            'product_id' => $product->id,
            'sku' => $product->sku,
        ]));
    }

    public function testStatusIsRequiredAndOfProductStatusEnum(): void
    {
        $this->assertHasErrors('status', new ProductRequest([
            'status' => null
        ]));

        $this->assertHasErrors('status', new ProductRequest([
            'status' => 'string'
        ]));

        $this->assertHasErrors('status', new ProductRequest([
            'status' => '2'
        ]));

        $this->assertNotHaveErrors('status', new ProductRequest([
            'status' => ProductStatus::Active
        ]));

        $this->assertNotHaveErrors('status', new ProductRequest([
            'status' => ProductStatus::Draft
        ]));
    }

    public function testPriceIsRequiredAndNumeric(): void
    {
        $this->assertHasErrors('price', new ProductRequest([
            'price' => null
        ]));

        $this->assertHasErrors('price', new ProductRequest([
            'price' => 'string'
        ]));

        $this->assertNotHaveErrors('price', new ProductRequest([
            'price' => '1.00'
        ]));
    }

    public function testCostIsNullableAndNumeric(): void
    {
        $this->assertNotHaveErrors('cost', new ProductRequest([
            'cost' => null
        ]));

        $this->assertHasErrors('cost', new ProductRequest([
            'cost' => 'string'
        ]));

        $this->assertNotHaveErrors('cost', new ProductRequest([
            'cost' => '1.00'
        ]));
    }

    public function testCategoryIdIsRequiredAndIntegerAndInValidValues(): void
    {
        $this->assertHasErrors('category_id', new ProductRequest([
            'category_id' => null
        ]));

        $this->assertHasErrors('category_id', new ProductRequest([
            'category_id' => 'string'
        ]));

        $this->assertHasErrors('category_id', new ProductRequest([
            'category_id' => '1'
        ]));

        // TODO: fix
//        $category = Category::factory()->create([
//            'type' => CategoryType::TYPE_PRODUCTS,
//        ]);
//        $this->assertNotHaveErrors('category_id', new ProductRequest([
//            'category_id' => $category->id,
//        ]));
    }

    public function testStockIsRequiredIfAndIsInteger(): void
    {
        $this->assertHasErrors('stock', new ProductRequest([
            'stock' => null
        ]));

        $this->assertHasErrors('stock', new ProductRequest([
            'stock' => 'string'
        ]));

        $this->assertNotHaveErrors('stock', new ProductRequest([
            'stock' => 123
        ]));
    }
}
