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
        [$superuser_role, $cashier_role, $camper_role] = $roles;

        User::factory()->state([
            'full_name' => 'Tadhg Boyle',
            'username' => 'tadhgboyle',
            'role_id' => $superuser_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(5)->state([
            'role_id' => $cashier_role->id,
            'password' => bcrypt('123456')
        ])->create();

        User::factory()->count(100)->state([
            'role_id' => $camper_role->id
        ])->create();
    }
}
