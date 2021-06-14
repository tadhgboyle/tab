<?php

namespace Tests\Feature;

use App\Models\Role;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    private Role $_superadmin_role; // 1
    private Role $_admin_role; // 2
    private Role $_manager_role; // 3
    private Role $_cashier_role; // 4
    private Role $_camper_role; // 5

    public function setUp(): void
    {
        parent::setUp();

        $this->_superadmin_role = Role::factory()->create();
        $this->_admin_role = Role::factory()->create([
            'name' => 'Admin',
            'superuser' => true,
            'staff' => true,
            'order' => 2
        ]);
        $this->_manager_role = Role::factory()->create([
            'name' => 'Manager',
            'superuser' => false,
            'staff' => true,
            'order' => 3,
            'permissions' => [
                'permission_node1',
                'permission_node2'
            ]
        ]);
        $this->_cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'superuser' => false,
            'staff' => true,
            'order' => 4
        ]);
        $this->_camper_role = Role::factory()->create([
            'name' => 'Camper',
            'superuser' => false,
            'staff' => false,
            'order' => 5
        ]);
    }

    public function testRolesAvailableWorksAsExpected()
    {
        $this->assertCount(5, $this->_superadmin_role->getRolesAvailable()); // all, including self
        $this->assertCount(5, $this->_admin_role->getRolesAvailable()); // all, including self
        $this->assertCount(2, $this->_manager_role->getRolesAvailable()); // cashier + camper
        $this->assertCount(1, $this->_cashier_role->getRolesAvailable()); // camper
        $this->assertEmpty($this->_camper_role->getRolesAvailable()); // none
    }

    public function testRolesAvailableWithCompareWorksAsExpected()
    {
        $this->markTestIncomplete();
    }

    public function testCanInteractWorksAsExpected()
    {
        $this->markTestIncomplete();
    }

    public function testHasPermissionWorksAsExpected()
    {
        $this->assertTrue($this->_superadmin_role->hasPermission('permission_node'));
        $this->assertTrue($this->_superadmin_role->hasPermission(['permission_node1', 'permission_node2', 'permission_node3']));

        $this->assertTrue($this->_manager_role->hasPermission('permission_node1'));
        $this->assertFalse($this->_manager_role->hasPermission('permission_node3'));
        $this->assertTrue($this->_manager_role->hasPermission(['permission_node1', 'permission_node2']));
        $this->assertFalse($this->_manager_role->hasPermission(['permission_node1', 'permission_node3']));
    }
}
