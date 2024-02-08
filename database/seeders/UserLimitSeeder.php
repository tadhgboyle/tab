<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\UserLimit;
use Illuminate\Database\Seeder;

class UserLimitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();

        foreach ($users as $user) {
            foreach ($categories as $category) {
                UserLimit::factory()->state([
                    'user_id' => $user->id,
                    'category_id' => $category->id
                ])->create();
            }
        }
    }
}
