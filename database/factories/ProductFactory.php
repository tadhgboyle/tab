<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => Arr::random(['Pop', 'Chips', 'Candy Bag']),
            'price' => Arr::random([1.25, 14.99, 4.98, 5.00]),
            'pst' => Arr::random([1, 0]),
            'deleted' => false,
            'stock' => Arr::random([0, 52, 14, 422, 987]),
            'unlimited_stock' => false,
            'box_size' => Arr::random([-1, 5, 8]),
            'stock_override' => false,
            'creator_id' => 1
        ];
    }
}
