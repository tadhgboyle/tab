<?php

namespace Database\Factories;

use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->words(2, true),
            'type' => CategoryType::TYPE_PRODUCTS,
        ];
    }
}
