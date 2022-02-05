<?php

namespace Database\Factories;

use App\Models\Category;
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
            'type' => CategoryType::TYPE_PRODUCTS,
        ];
    }
}
