<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(array $categories)
    {
        [, , $activities_category, $general_category] = $categories;

        Activity::factory()->count(5)->state([
            'category_id' => $activities_category->id
        ])->create();

        Activity::factory()->count(5)->state([
            'category_id' => $general_category->id
        ])->create();
    }
}
