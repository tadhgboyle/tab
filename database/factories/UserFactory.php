<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $full_name = $this->faker->unique()->firstName . ' ' . $this->faker->lastName;

        return [
            'full_name' => $full_name,
            'username' => str($full_name)->lower()->replace(' ', '') . ($this->faker->boolean(25) ? $this->faker->numberBetween(1, 100) : ''),
            'balance' => $this->faker->numberBetween(0, 1000_00)
        ];
    }
}
