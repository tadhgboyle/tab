<?php

namespace Tests\Feature\Product;

use App\Casts\CategoryType;
use App\Models\Product;
use App\Models\Category;
use Tests\FormRequestTestCase;
use App\Http\Requests\ProductRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndHasMinAndUnique(): void
    {
        $this->assertHasErrors('name', new ProductRequest([
            'name' => null
        ]));

        $this->assertHasErrors('name', new ProductRequest([
            'name' => '1'
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

    public function testStockIsRequiredAndIsInteger(): void
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

    public function testBoxSizeIsInValidValues(): void
    {
        $this->assertHasErrors('box_size', new ProductRequest([
            'box_size' => '0'
        ]));

        $this->assertNotHaveErrors('box_size', new ProductRequest([
            'box_size' => '1'
        ]));
    }
}
