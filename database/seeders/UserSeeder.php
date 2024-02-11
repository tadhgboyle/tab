<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(array $roles): void
    {
        [$superuser_role, $manager_role, $finance_manager_role, $cashier_role, $camper_role] = $roles;

        User::factory()->state([
            'email' => 'tadhgsmboyle@gmail.com',
            'name' => 'Tadhg Boyle',
            'role_id' => $superuser_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->state([
            'name' => 'Taryn Pivarnyik',
            'role_id' => $superuser_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(5)->state([
            'role_id' => $manager_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(5)->state([
            'role_id' => $finance_manager_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(5)->state([
            'role_id' => $cashier_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(200)->state([
            'role_id' => $camper_role->id
        ])->create();
    }
}
