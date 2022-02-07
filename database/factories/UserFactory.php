<?php

namespace Database\Factories;

use Str;
use App\Models\User;
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
            'username' => Str::of($full_name)->lower()->replace(' ', '') . ($this->faker->boolean(25) ? $this->faker->numberBetween(1, 100) : ''),
            'balance' => $this->faker->randomFloat(2, 5, 600)
        ];
    }
}
