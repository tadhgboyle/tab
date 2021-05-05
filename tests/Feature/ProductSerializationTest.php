<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $serialized = $product->id . '*1$' . $product->price . 'G1.08P1.04R0';
        
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

        $serialized = '1*1$1.50G1.08P1.04R0';

        TransactionController::deserializeProduct($serialized, true);
    }
}
