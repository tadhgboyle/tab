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

        return [
            'product_id' => $product->id,
            'product_variant_id' => $product->hasVariants() ? $product->variants->random()->id : null,
            'quantity' => random_int(1, 25),
            'cost' => $cost,
        ];
    }
}
