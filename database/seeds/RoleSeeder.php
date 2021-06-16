<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return array
     */
    public function run(): array
    {
        $superuser_role = Role::factory()->create();

        $cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'staff' => true,
            'superuser' => false,
            'order' => 2,
            'permissions' => [
                'cashier',
                'cashier_create'
            ]
        ]);

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 3
        ]);

        return [$superuser_role, $cashier_role, $camper_role];
    }
}
