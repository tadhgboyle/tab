<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Enums\CategoryType;
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
            'type' => CategoryType::Products
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch',
            'type' => CategoryType::Products
        ]);

        $activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::Activities
        ]);

        $general_category = Category::factory()->create([
            'name' => 'General',
            'type' => CategoryType::ProductsActivities
        ]);

        return [$food_category, $merch_category, $activities_category, $general_category];
    }
}
