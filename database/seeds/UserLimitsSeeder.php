<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use App\Models\UserLimits;
use Illuminate\Database\Seeder;

class UserLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $categories = Category::all();

        foreach ($users as $user) {

            foreach ($categories as $category) {

                UserLimits::factory()->state([
                    'user_id' => $user->id,
                    'category_id' => $category->id
                ])->create();

            }
        }
    }
}
