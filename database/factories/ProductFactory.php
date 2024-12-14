<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $price = $this->faker->boolean(5) ? 0_00 : $this->faker->numberBetween(0, 50_00);
        // round to nearest $0.25
        $price = round($price / 25) * 25;
        $price = $price / 100;

        $cost = $this->faker->boolean ? null : $this->faker->numberBetween(0, $price * 100);
        $cost = round($cost / 25) * 25;
        $cost = $cost / 100;

        return [
            'name' => $this->faker->unique()->words($this->faker->numberBetween(1, 2), true),
            'sku' => $this->faker->boolean ? Str::upper(Str::random(6)) : null,
            'status' => ProductStatus::Active,
            'price' => $price,
            'cost' => $cost === 0 && random_int(0, 1) ? null : $cost,
            'pst' => $this->faker->boolean,
            'stock' => $this->faker->numberBetween(10, 300),
            'unlimited_stock' => $this->faker->boolean,
            'box_size' => $this->faker->boolean ? -1 : $this->faker->numberBetween(4, 25),
            'stock_override' => $this->faker->boolean,
            'restore_stock_on_return' => $this->faker->boolean,
        ];
    }
}
