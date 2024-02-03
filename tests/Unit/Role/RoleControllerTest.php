<?php

namespace Tests\Unit\Role;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private Role $_superadmin_role;
    private Role $_cashier_role;
    private Role $_manager_role;
    private Role $_camper_role;
    private User $_superuser;
    private User $_cashier;
    private User $_manager;

    public function setUp(): void
    {
        parent::setUp();

        $this->_superadmin_role = Role::factory()->create([
            'name' => 'Superadmin',
            'order' => 1,
            'superuser' => true,
        ]);

        $this->_manager_role = Role::factory()->create([
            'name' => 'Manager',
            'order' => 2,
            'superuser' => false,
            'permissions' => [
                'settings',
                'settings_roles_manage',
            ],
        ]);

        $this->_cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'order' => 3,
            'superuser' => false,
        ]);

        $this->_camper_role = Role::factory()->create([
            'name' => 'Camper',
            'order' => 4,
            'staff' => false,
            'superuser' => false,
        ]);

        $this->_cashier = User::factory()->create([
            'full_name' => 'Cashier User',
            'role_id' => $this->_cashier_role->id,
        ]);

        $this->_superuser = User::factory()->create([
            'full_name' => 'Superuser User',
            'role_id' => $this->_superadmin_role->id,
        ]);

        $this->_manager = User::factory()->create([
            'full_name' => 'Manager User',
            'role_id' => $this->_manager_role->id,
        ]);

        $this->expectPermissionChecks(['settings', 'settings_roles_manage']);
    }

    public function testCanViewRoleCreatePage(): void
    {
        $this->actingAs($this->_superuser)
            ->get(route('settings_roles_create'))
            ->assertOk()
            ->assertViewIs('pages.settings.roles.form');
    }

    public function testCanCreateBasicRole(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, $params);
    }

    public function testSetsNewRoleStaffIfSelected(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, $params);
    }

    public function testDoesNotSetNewRoleSuperuserIfStaffIsNotSelected(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
            'superuser' => true,
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Test Role',
            'staff' => false,
            'superuser' => false,
        ]);
    }

    public function testSetsNewRoleSuperuserIfStaffIsSelected(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
            'superuser' => true,
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, $params);
    }

    public function testSetsNewRolePermissionsIfStaff(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
            'permissions' => [
                'settings' => true,
                'settings_roles_manage' => true,
            ],
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Test Role',
            'permissions' => json_encode([
                'settings',
                'settings_roles_manage',
            ]),
        ]);
    }

    public function testDoesNotSetNewRolePermissionsIfNotStaff(): void
    {
        $params = [
            'name' => 'Test Role',
            'order' => Role::max('order') + 1,
            'permissions' => [
                'settings' => true,
                'settings_roles_manage' => true,
            ],
        ];

        $this->actingAs($this->_superuser)
            ->post(route('settings_roles_store'), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Created role Test Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Test Role',
            'permissions' => '[]',
        ]);
    }

    public function testCanViewRoleEditPage(): void
    {
        $this->actingAs($this->_superuser)
            ->get(route('settings_roles_edit', $this->_cashier_role->id))
            ->assertOk()
            ->assertViewIs('pages.settings.roles.form')
            ->assertSee("<strong>Role:</strong> {$this->_cashier_role->name}", false);
    }

    public function testCannotViewRoleEditPageIfCannotInteractWithRole(): void
    {
        $this->actingAs($this->_manager)
            ->get(route('settings_roles_edit', $this->_superadmin_role->id))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'You cannot interact with that role.');
    }

    public function testRoleEditPageHasCorrectAffectedUsersValue(): void
    {
        $response = $this->actingAs($this->_superuser)
            ->get(route('settings_roles_edit', $this->_cashier_role->id))
            ->assertOk()
            ->assertViewIs('pages.settings.roles.form')
            ->assertSee('<strong>1</strong> user  currently have this role.', false);

        $view_data = $response->getOriginalContent()->getData();

        $this->assertEquals([$this->_cashier->id], $view_data['affected_users']->pluck('id')->all());
    }

    public function testRoleEditPageHasCorrectAvailableRolesValue(): void
    {
        $response = $this->actingAs($this->_superuser)
            ->get(route('settings_roles_edit', $this->_cashier_role->id))
            ->assertOk()
            ->assertViewIs('pages.settings.roles.form');

        foreach ([$this->_manager_role, $this->_superadmin_role] as $role) {
            $response->assertSee("<option value=\"{$role->id}\">{$role->name}</option>", false);
        }

        $view_data = $response->getOriginalContent()->getData();

        $this->assertEquals(
            [$this->_camper_role->id, $this->_manager_role->id, $this->_superadmin_role->id],
            array_column($view_data['available_roles'], 'id')
        );
    }

    public function testCanUpdateRole(): void
    {
        $params = [
            'name' => 'Updated Cashier Role',
            'order' => Role::max('order') + 1,
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_cashier_role->id), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Cashier Role.');

        $this->assertDatabaseHas(Role::class, [
            'id' => $this->_cashier_role->id,
            'name' => 'Updated Cashier Role',
            'order' => $params['order'],
        ]);
    }

    public function testCannotUpdateRoleIfCannotInteractWithRole(): void
    {
        $this->actingAs($this->_manager)
            ->get(route('settings_roles_update', $this->_superadmin_role->id))
            ->assertRedirect(route('settings'))
            ->assertSessionHas('error', 'You cannot interact with that role.');
    }

    public function testUpdatesRoleAsStaffIfSelected(): void
    {
        $params = [
            'name' => 'Updated Camper Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_camper_role->id), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Camper Role.');

        $this->assertDatabaseHas(Role::class, $params);
    }

    public function testDoesNotUpdateRoleToSuperuserIfStaffIsNotSelected(): void
    {
        $params = [
            'name' => 'Updated Camper Role',
            'order' => Role::max('order') + 1,
            'superuser' => true,
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_camper_role->id), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Camper Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Updated Camper Role',
            'staff' => false,
            'superuser' => false,
        ]);
    }

    public function testUpdatesRoleToSuperuserIfStaffIsSelected(): void
    {
        $params = [
            'name' => 'Updated Manager Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
            'superuser' => true,
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_manager_role), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Manager Role.');

        $this->assertDatabaseHas(Role::class, $params);
    }

    public function testSetsUpdatedRolePermissionsIfStaff(): void
    {
        $params = [
            'name' => 'Updated Camper Role',
            'order' => Role::max('order') + 1,
            'staff' => true,
            'permissions' => [
                'settings' => true,
                'settings_roles_manage' => true,
            ],
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_camper_role->id), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Camper Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Updated Camper Role',
            'permissions' => json_encode([
                'settings',
                'settings_roles_manage',
            ]),
        ]);
    }

    public function testDoesNotUpdateRolePermissionsIfNotStaff(): void
    {
        $params = [
            'name' => 'Updated Camper Role',
            'order' => Role::max('order') + 1,
            'permissions' => [
                'settings' => true,
                'settings_roles_manage' => true,
            ],
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_update', $this->_camper_role->id), $params)
            ->assertRedirect(route('settings'))
            ->assertSessionHas('success', 'Edited role Updated Camper Role.');

        $this->assertDatabaseHas(Role::class, [
            'name' => 'Updated Camper Role',
            'permissions' => '[]',
        ]);
    }

    public function testCanReorderRoles(): void
    {
        $params = [
            'roles' => json_encode([
                $this->_camper_role->id,
                $this->_cashier_role->id,
                $this->_manager_role->id,
                $this->_superadmin_role->id,
            ]),
        ];

        $this->actingAs($this->_superuser)
            ->put(route('settings_roles_order_ajax'), $params)
            ->assertOk();

        $this->assertDatabaseHas(Role::class, [
            'id' => $this->_camper_role->id,
            'order' => 1,
        ]);

        $this->assertDatabaseHas(Role::class, [
            'id' => $this->_cashier_role->id,
            'order' => 2,
        ]);

        $this->assertDatabaseHas(Role::class, [
            'id' => $this->_manager_role->id,
            'order' => 3,
        ]);

        $this->assertDatabaseHas(Role::class, [
            'id' => $this->_superadmin_role->id,
            'order' => 4,
        ]);
    }
}
