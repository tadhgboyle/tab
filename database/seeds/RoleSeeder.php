<?php

namespace Database\Seeders;

use App\Helpers\Permission;
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
                Permission::CASHIER,
                Permission::CASHIER_CREATE
            ]
        ]);

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 3,
            'permissions' => []
        ]);

        return [$superuser_role, $cashier_role, $camper_role];
    }
}
