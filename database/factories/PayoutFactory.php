<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'identifier' => $this->faker->unique()->randomNumber(8),
        ];
    }
}
