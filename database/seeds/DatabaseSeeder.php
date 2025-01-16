<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\PurchaseOrderSeeder;
use Database\Seeders\SupplierSeeder;
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

        $this->command->info('Seeding Suppliers...');
        $this->resolve(SupplierSeeder::class)->run();

        $this->command->info('Seeding Purchase Orders...');
        $this->resolve(PurchaseOrderSeeder::class)->run();
    }
}
