<?php

use Database\Seeders\ActivitySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $roles = $this->resolve(RoleSeeder::class)->run();

        $this->resolve(UserSeeder::class)->run($roles);
        
        $categories = $this->resolve(CategorySeeder::class)->run();

        // User Limits

        $this->resolve(SettingsSeeder::class)->run();

        $this->resolve(ProductSeeder::class)->run($categories);

        // Implement Categories to Activities
        $this->resolve(ActivitySeeder::class)->run($categories);

        // Transactions

        // Activity Transactions
    }
}
