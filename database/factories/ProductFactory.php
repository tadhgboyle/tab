<?php

namespace Database\Factories;

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
        return [
            'name' => $this->faker->unique()->words($this->faker->numberBetween(1, 2), true),
            'price' => $this->faker->boolean(15) ? 0.00 : $this->faker->randomFloat(2, 0.00, 50.00),
            'pst' => $this->faker->boolean,
            'stock' => $this->faker->numberBetween(10, 300),
            'unlimited_stock' => $this->faker->boolean,
            'box_size' => $this->faker->boolean ? -1 : $this->faker->numberBetween(4, 25),
            'stock_override' => $this->faker->boolean,
        ];
    }
}
