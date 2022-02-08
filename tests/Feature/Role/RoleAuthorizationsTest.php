<?php

namespace Tests\Feature\Role;

use Tests\TestCase;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAuthorizationsTest extends TestCase
{
    use RefreshDatabase;

    private Role $_superadmin_role; // 6
    private Role $_admin_role; // 5
    private Role $_manager_role; // 4
    private Role $_cashier_role; // 3
    private Role $_camper_parent_role; // 2
    private Role $_camper_role; // 1

    public function setUp(): void
    {
        parent::setUp();

        $this->_superadmin_role = Role::factory()->create([
            'order' => 6
        ]);
        $this->_admin_role = Role::factory()->create([
            'name' => 'Admin',
            'superuser' => true,
            'staff' => true,
            'order' => 5
        ]);
        $this->_manager_role = Role::factory()->create([
            'name' => 'Manager',
            'superuser' => false,
            'staff' => true,
            'order' => 4,
            'permissions' => [
                'permission_node1',
                'permission_node2'
            ]
        ]);
        $this->_cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'superuser' => false,
            'staff' => true,
            'order' => 3
        ]);
        $this->_camper_parent_role = Role::factory()->create([
            'name' => 'Camper (Parent)',
            'superuser' => false,
            'staff' => false,
            'order' => 2
        ]);
        $this->_camper_role = Role::factory()->create([
            'name' => 'Camper',
            'superuser' => false,
            'staff' => false,
            'order' => 1
        ]);
    }

    public function testRolesAvailableWorksAsExpected(): void
    {
        $this->assertCount(6, $this->_superadmin_role->getRolesAvailable()); // all, including self
        $this->assertCount(6, $this->_admin_role->getRolesAvailable()); // all, including self
        $this->assertCount(3, $this->_manager_role->getRolesAvailable()); // cashier + camper parent + camper
        $this->assertCount(2, $this->_cashier_role->getRolesAvailable()); // camper parent + camper
        $this->assertEmpty($this->_camper_parent_role->getRolesAvailable()); // none
        $this->assertEmpty($this->_camper_role->getRolesAvailable()); // none
    }

    public function testRolesAvailableWithCompareWorksAsExpected(): void
    {
        $this->assertCount(3, $this->_superadmin_role->getRolesAvailable($this->_manager_role)); // camper parent + camper + cashier
        $this->assertCount(3, $this->_admin_role->getRolesAvailable($this->_manager_role)); // camper parent + camper + cashier
        $this->assertCount(3, $this->_manager_role->getRolesAvailable($this->_manager_role)); // cashier + camper parent + camper
        $this->assertCount(2, $this->_cashier_role->getRolesAvailable($this->_manager_role)); // camper parent + camper
        $this->assertCount(1, $this->_camper_parent_role->getRolesAvailable($this->_manager_role)); // camper
        $this->assertCount(1, $this->_camper_role->getRolesAvailable($this->_manager_role)); // camper parent
    }

    public function testCanInteractWorksAsExpected(): void
    {
        $this->assertTrue($this->_superadmin_role->canInteract($this->_manager_role));

        $this->assertFalse($this->_manager_role->canInteract($this->_superadmin_role));
        $this->assertTrue($this->_manager_role->canInteract($this->_cashier_role));

        $this->assertFalse($this->_camper_parent_role->canInteract($this->_camper_role));
    }

    public function testHasPermissionWorksAsExpected(): void
    {
        $this->assertTrue($this->_superadmin_role->hasPermission('permission_node'));
        $this->assertTrue($this->_superadmin_role->hasPermission(['permission_node1', 'permission_node2', 'permission_node3']));

        $this->assertTrue($this->_manager_role->hasPermission('permission_node1'));
        $this->assertFalse($this->_manager_role->hasPermission('permission_node3'));
        $this->assertTrue($this->_manager_role->hasPermission(['permission_node1', 'permission_node2']));
        $this->assertFalse($this->_manager_role->hasPermission(['permission_node1', 'permission_node3']));

        $this->assertFalse($this->_cashier_role->hasPermission('permission_node1'));
        $this->assertFalse($this->_cashier_role->hasPermission(['permission_node1', 'permission_node2']));
    }
}
