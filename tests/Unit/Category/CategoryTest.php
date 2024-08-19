<?php

namespace Tests\Unit\Category;

use stdClass;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function testTypeIsCastedToCategoryTypeClass(): void
    {
        foreach (
            [
                CategoryType::ProductsActivities->value => 'Products & Activities',
                CategoryType::Products->value => 'Products',
                CategoryType::Activities->value => 'Activities',
            ] as $type => $name) {
            $category = Category::factory()->create([
                'type' => $type,
            ]);

            $this->assertEquals($type, $category->type->value);
            $this->assertEquals($name, $category->type->getName());
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
