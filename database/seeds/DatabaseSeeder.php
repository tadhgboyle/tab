<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PayoutSeeder;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\App;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RotationSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UserLimitsSeeder;
use Database\Seeders\TransactionSeeder;
use Database\Seeders\ActivityTransactionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        if (App::isProduction()) {
            $this->command->info('Production environment detected, only seeding admin user...');

            User::factory()->state([
                'full_name' => 'admin',
                'username' => 'admin',
                'role_id' => Role::factory()->create()->id,
                'password' => bcrypt('123456')
            ])->create();

            return;
        }

        $this->command->info('Seeding Roles...');
        $roles = $this->resolve(RoleSeeder::class)->run();

        $this->command->info('Seeding Users...');
        $this->resolve(UserSeeder::class)->run($roles);

        $this->command->info('Seeding Categories...');
        $categories = $this->resolve(CategorySeeder::class)->run();

        $this->command->info('Seeding Settings...');
        $this->resolve(SettingsSeeder::class)->run();

        $this->command->info('Seeding Products...');
        $this->resolve(ProductSeeder::class)->run($categories);

        $this->command->info('Seeding Activities...');
        $this->resolve(ActivitySeeder::class)->run($categories);

        $this->command->info('Seeding UserLimits...');
        $this->resolve(UserLimitsSeeder::class)->run();

        $this->command->info('Seeding Rotations...');
        $this->resolve(RotationSeeder::class)->run();

        $this->command->info('Seeding Transactions...');
        $this->resolve(TransactionSeeder::class)->run();

        $this->command->info('Seeding Activity Transactions...');
        $this->resolve(ActivityTransactionSeeder::class)->run();

        $this->command->info('Seeding User Payouts...');
        $this->resolve(PayoutSeeder::class)->run();
    }
}
