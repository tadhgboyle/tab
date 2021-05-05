<?php

namespace Tests\Feature;

use App\Http\Controllers\TransactionController;
use Tests\TestCase;

class ProductSerializationTest extends TestCase
{
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
            'returned' => '1'
        ], $deserialized);
    }

    /**
     * Tests that the TransactionController::deserializeProduct function works, accesses database.
     */
    public function testProductDeserializationFull()
    {
        // TODO
    }
}
