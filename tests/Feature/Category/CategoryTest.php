<?php

namespace Tests\Feature\Category;

use stdClass;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Casts\CategoryType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function testTypeIsCastedToCategoryTypeClass(): void
    {
        foreach (
            [
                CategoryType::TYPE_PRODUCTS_ACTIVITIES => 'Products & Activities',
                CategoryType::TYPE_PRODUCTS => 'Products',
                CategoryType::TYPE_ACTIVITIES => 'Activities',
            ] as $type => $name) {
            $category = Category::factory()->create([
                'type' => $type,
            ]);

            $this->assertInstanceOf(stdClass::class, $category->type);
            $this->assertEquals($type, $category->type->id);
            $this->assertEquals($name, $category->type->name);
        }
    }

    public function testHasManyProducts(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertCount(1, $category->products);
        $this->assertEquals($product->id, $category->products->first()->id);
    }
}
