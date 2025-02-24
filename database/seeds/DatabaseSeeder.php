<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\FamilySeeder;
use Database\Seeders\PayoutSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Support\Facades\App;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\GiftCardSeeder;
use Database\Seeders\RotationSeeder;
use Database\Seeders\SettingsSeeder;
use Database\Seeders\UserLimitSeeder;
use Database\Seeders\ActivityRegistrationSeeder;

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

            if (User::where('username', 'admin')->exists()) {
                $this->command->info('Admin user already exists, skipping...');
                return;
            }

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

        $this->command->info('Seeding Families...');
        $this->resolve(FamilySeeder::class)->run();

        $this->command->info('Seeding Categories...');
        $categories = $this->resolve(CategorySeeder::class)->run();

        $this->command->info('Seeding Settings...');
        $this->resolve(SettingsSeeder::class)->run();

        $this->command->info('Seeding Products...');
        $this->resolve(ProductSeeder::class)->run($categories);

        $this->command->info('Seeding Activities...');
        $this->resolve(ActivitySeeder::class)->run($categories);

        $this->command->info('Seeding UserLimits...');
        $this->resolve(UserLimitSeeder::class)->run();

        $this->command->info('Seeding Rotations...');
        $this->resolve(RotationSeeder::class)->run();

        $this->command->info('Seeding Gift Cards...');
        $this->resolve(GiftCardSeeder::class)->run();

        $this->command->info('Seeding Orders...');
        $this->resolve(OrderSeeder::class)->run();

        $this->command->info('Seeding Activity Registrations...');
        $this->resolve(ActivityRegistrationSeeder::class)->run();

        $this->command->info('Seeding User Payouts...');
        $this->resolve(PayoutSeeder::class)->run();
    }
}
