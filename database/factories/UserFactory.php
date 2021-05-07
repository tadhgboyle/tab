<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'full_name' => $this->faker->name(),
            'username' => $this->faker->userName,
            'balance' => $this->faker->numberBetween(10, 500),
            'password' => '$2y$10$/7i2HBJUlF6GzapYti0xhO82PH6xfGmOWRuOBdN0nCfjZXqSCaVvC', // "123456"
        ];
    }
}
