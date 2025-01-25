<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderProduct>
 */
class PurchaseOrderProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::query()->where('unlimited_stock', false)->get()->random();

        $cost = $this->faker->boolean ? null : $this->faker->numberBetween(0, 50_00);
        $cost = round($cost / 25) * 25;
        $cost = $cost / 100;

        $quantity = $this->faker->numberBetween(1, 25);
        $receivedQuantity = $this->faker->boolean(30) ? $this->faker->numberBetween(0, $quantity) : 0;

        return [
            'product_id' => $product->id,
            'product_variant_id' => $product->hasVariants() ? $product->variants->random()->id : null,
            'quantity' => $quantity,
            'received_quantity' => $receivedQuantity,
            'cost' => $cost,
        ];
    }
}
