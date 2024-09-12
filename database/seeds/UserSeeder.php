<?php

namespace Database\Seeders;

use App\Enums\FamilyMemberRole;
use App\Models\Family;
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

        $tadhg = User::factory()->state([
            'full_name' => 'Tadhg Boyle',
            'username' => 'admin',
            'role_id' => $superuser_role->id,
            'password' => bcrypt('123456')
        ])->create();

        $taryn = User::factory()->state([
            'full_name' => 'Taryn Pivarnyik',
            'username' => 'taryn',
            'role_id' => $superuser_role->id,
            'password' => bcrypt('123456')
        ])->create();

        $family = Family::factory()->create([
            'name' => 'Boyles',
        ]);

        $family->members()->createMany([
            [
                'user_id' => $tadhg->id,
                'role' => FamilyMemberRole::Admin,
            ],
            [
                'user_id' => $taryn->id,
                'role' => FamilyMemberRole::Member,
            ],
        ]);

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
