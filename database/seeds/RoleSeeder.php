<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Helpers\Permission;
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

        $manager_role = Role::factory()->create([
            'name' => 'Manager',
            'staff' => true,
            'superuser' => false,
            'order' => 2,
            'permissions' => [
                Permission::CASHIER,
                Permission::CASHIER_CREATE,
                Permission::USERS,
                Permission::USERS_LIST,
                Permission::USERS_VIEW,
                Permission::USERS_MANAGE,
                Permission::PRODUCTS,
                Permission::PRODUCTS_LIST,
                Permission::PRODUCTS_MANAGE,
                Permission::ACTIVITIES,
                Permission::ACTIVITIES_LIST,
                Permission::ACTIVITIES_VIEW,
                Permission::ACTIVITIES_REGISTER_USER,
                Permission::ACTIVITIES_MANAGE,
                Permission::ORDERS,
                Permission::ORDERS_LIST,
                Permission::ORDERS_VIEW,
                Permission::SETTINGS,
                Permission::SETTINGS_CATEGORIES_MANAGE,
                Permission::SETTINGS_ROTATIONS_MANAGE,
            ]
        ]);

        $finance_manager_role = Role::factory()->create([
            'name' => 'Finance Manager',
            'staff' => true,
            'superuser' => false,
            'order' => 3,
            'permissions' => [
                Permission::USERS,
                Permission::USERS_LIST,
                Permission::USERS_VIEW,
                Permission::USERS_MANAGE,
                Permission::USERS_PAYOUTS_CREATE,
                Permission::PRODUCTS,
                Permission::PRODUCTS_LIST,
                Permission::PRODUCTS_MANAGE,
                Permission::ACTIVITIES,
                Permission::ACTIVITIES_LIST,
                Permission::ACTIVITIES_VIEW,
                Permission::ORDERS,
                Permission::ORDERS_LIST,
                Permission::ORDERS_VIEW,
                Permission::ORDERS_RETURN,
                Permission::SETTINGS,
                Permission::SETTINGS_GENERAL,
                Permission::STATISTICS,
                Permission::STATISTICS_ACTIVITY_SALES,
                Permission::STATISTICS_PRODUCT_SALES,
                Permission::STATISTICS_ORDER_HISTORY,
                Permission::STATISTICS_INCOME_INFO,
            ]
        ]);

        $cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'staff' => true,
            'superuser' => false,
            'order' => 4,
            'permissions' => [
                Permission::CASHIER,
                Permission::CASHIER_CREATE,
                Permission::ACTIVITIES,
                Permission::ACTIVITIES_LIST,
                Permission::ACTIVITIES_VIEW,
                Permission::ACTIVITIES_REGISTER_USER,
            ]
        ]);

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 5,
            'permissions' => []
        ]);

        return [$superuser_role, $manager_role, $finance_manager_role, $cashier_role, $camper_role];
    }
}
