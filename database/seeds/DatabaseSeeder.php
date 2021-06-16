<?php

use Database\Seeders\ActivitySeeder;
use Database\Seeders\ActivityTransactionSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\UserLimitsSeeder;
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

        $this->resolve(SettingsSeeder::class)->run();

        $this->resolve(ProductSeeder::class)->run($categories);

        $this->resolve(ActivitySeeder::class)->run($categories);

        $this->resolve(UserLimitsSeeder::class)->run();

        $this->resolve(TransactionSeeder::class)->run();

        $this->resolve(ActivityTransactionSeeder::class)->run();
    }
}
