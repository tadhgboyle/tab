<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private static array $foodNames = [
        'Pop', 'Chips', 'Candy Bag', 'Slurpee (Small)', 'Slurpee (Large)', 'Coffee',
        'Hot Dog', 'Ice Cream (1 scoop)', 'Ice Cream (2 scoops)', 'Ice Cream (3 scoops)',
        'Chocolate Bar', 'M&Ms', 'Skittles', 'Nerds', 'Gatorade', 'Ice Cream Sandwich', 'Rockets'
    ];

    private static array $merchNames = [
        'Sweater', 'Hoodie', 'Hat', 'Pants', 'Fanny Pack', 'Sunglasses',
        'T Shirt', 'Skimboard', 'Waterbottle', 'Shorts', 'Long Sleeve Shirt'
    ];

    private static array $generalNames = [
        'Ceramic', 'Tube Ride', 'Sunscreen', 'Tooth Brush', 'Boat Rental',
        'Wakeboard (1 hour)', 'Floss', 'Tye Die Kit', 'Smores Kit',
        'Family Photo', 'Computer Pass (1 hour)'
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(array $categories): void
    {
        [$food_category, $merch_category, , $general_category] = $categories;

        foreach (self::$foodNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'category_id' => $food_category
            ])->create();
        }

        foreach (self::$merchNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'category_id' => $merch_category
            ])->create();
        }

        foreach (self::$generalNames as $name) {
            Product::factory()->state([
                'name' => $name,
                'category_id' => $general_category
            ])->create();
        }
    }
}
