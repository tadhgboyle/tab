<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Casts\CategoryType;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): array
    {
        $food_category = Category::factory()->create([
            'name' => 'Food',
            'type' => CategoryType::TYPE_PRODUCTS
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch',
            'type' => CategoryType::TYPE_PRODUCTS
        ]);

        $activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::TYPE_ACTIVITIES
        ]);

        $general_category = Category::factory()->create([
            'name' => 'General',
            'type' => CategoryType::TYPE_PRODUCTS_ACTIVITIES
        ]);

        return [$food_category, $merch_category, $activities_category, $general_category];
    }
}
