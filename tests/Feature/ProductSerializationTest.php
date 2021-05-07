<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use App\Http\Controllers\TransactionController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductSerializationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Tests that the TransactionController::serializeProduct function works.
     */
    public function testProductSerialization()
    {
        $serialized = TransactionController::serializeProduct(34, 2, 1.45, 1.08, 1.04, 1);

        $this->assertEquals('34*2$1.45G1.08P1.04R1', $serialized);
    }

    /**
     * Tests that the TransactionController::deserializeProduct function works, does not access database.
     */
    public function testProductDeserializationNotFull()
    {
        $serialized = '34*2$1.45G1.08P1.04R1';

        $deserialized = TransactionController::deserializeProduct($serialized, false);

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

    /**
     * Tests that the TransactionController::deserializeProduct function works, accesses database.
     */
    public function testProductDeserializationFull()
    {
        $category = Category::factory()->create([
            'name' => 'Food',
            'type' => 2
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id
        ]);

        $serialized = TransactionController::serializeProduct($product->id, 1, $product->price, 1.08, 1.04, 0);

        $deserialized = TransactionController::deserializeProduct($serialized, true);

        $this->assertEquals($deserialized['name'], $product->name);
        $this->assertEquals($deserialized['category'], $product->category_id);
    }

    /**
     * Tests that the TransactionController::deserializeProduct function throws exception
     * when non existant product ID is sent, and $full is true.
     */
    public function testProductDeserializationThrowsException()
    {
        $this->expectException(ModelNotFoundException::class);

        $serialized = TransactionController::serializeProduct(1, 1, 1.49, 1.08, 1.04, 0);

        TransactionController::deserializeProduct($serialized, true);
    }
}
