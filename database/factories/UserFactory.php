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
        $name = $this->faker->unique()->firstName . ' ' . $this->faker->lastName;

        return [
            'email' => $this->faker->unique()->safeEmail,
            'name' => $name,
            'balance' => $this->faker->numberBetween(0, 1000)
        ];
    }
}
