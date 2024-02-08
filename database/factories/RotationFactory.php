<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RotationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'start' => now(),
            'end' => now()->addDays(7),
        ];
    }
}
