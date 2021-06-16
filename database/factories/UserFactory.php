<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $full_name = $this->faker->unique()->name();

        return [
            'full_name' => $full_name,
            'username' => $this->faker->boolean ? $this->faker->userName : Str::of($full_name)->lower()->replace(' ', ''),
            'balance' => $this->faker->randomFloat(2, 10, 500)
        ];
    }
}
