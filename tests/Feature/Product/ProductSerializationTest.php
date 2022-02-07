<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Helpers\ProductHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductSerializationTest extends TestCase
{
    use RefreshDatabase;

    public function testProductSerialization(): void
    {
        $serialized = ProductHelper::serializeProduct(34, 2, 1.45, 1.08, 1.04, 1);

        $this->assertEquals('34*2$1.45G1.08P1.04R1', $serialized);
    }

    public function testProductDeserializationNotFull(): void
    {
        $serialized = '34*2$1.45G1.08P1.04R1';

        $deserialized = ProductHelper::deserializeProduct($serialized, false);

        $this->assertEquals([
            'id' => '34',
            'name' => '',
            'category' => '',
            'quantity' => '2',
            'price' => '1.45',
            'gst' => '1.08',
            'pst' => '1.04',
            'returned' => '1',
        ], $deserialized);
    }

    public function testProductDeserializationFull(): void
    {
        $category = Category::factory()->create([
            'name' => 'Food',
            'type' => 2
        ]);

        $product = Product::factory()->create([
            'name' => 'Fake Item',
            'category_id' => $category->id
        ]);

        $serialized = ProductHelper::serializeProduct($product->id, 1, $product->price, 1.08, 1.04, 0);

        $deserialized = ProductHelper::deserializeProduct($serialized, true);

        $this->assertEquals($deserialized['name'], $product->name);
        $this->assertEquals($deserialized['category'], $product->category_id);
    }

    public function testProductDeserializationThrowsException(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $serialized = ProductHelper::serializeProduct(1, 1, 1.49, 1.08, 1.04, 0);

        ProductHelper::deserializeProduct($serialized, true);
    }
}
