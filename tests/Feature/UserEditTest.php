<?php

namespace Tests\Feature;

use Hash;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Services\Users\UserEditService;
use App\Services\Users\UserCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserEditTest extends TestCase
{
    use RefreshDatabase;

    private Role $_superadmin_role;
    private Role $_cashier_role;
    private Role $_camper_role;
    private User $_superadmin_user;
    private User $_cashier_user;

    public function setUp(): void
    {
        parent::setUp();

        [$superadmin_role, $cashier_role, $camper_role] = $this->createRoles();
        $this->_superadmin_role = $superadmin_role;
        $this->_cashier_role = $cashier_role;
        $this->_camper_role = $camper_role;

        $this->_superadmin_user = $this->createSuperadminUser($this->_superadmin_role);
        $this->_cashier_user = $this->createCashierUser($this->_cashier_role);
    }

    public function testCannotEditUsersWithHigherRoleThanSelf()
    {
        $this->actingAs($this->_cashier_user);

        $userService = new UserEditService($this->createRequest(
            id: $this->_superadmin_user->id,
            role_id: $this->_superadmin_role->id
        ));

        $this->assertSame(UserEditService::RESULT_CANT_MANAGE_THAT_ROLE, $userService->getResult());
    }

    public function testUpdatesAttributesProperlyWhenRoleIsSameOrBothStaffRoles()
    {
        $this->actingAs($this->_superadmin_user);

        foreach ([$this->_cashier_role, $this->_superadmin_role] as $role) {
            $userService = new UserEditService($this->createRequest(
                id: $this->_cashier_user->id,
                role_id: $role->id,
                full_name: 'Ronan Boyle 1',
                username: $this->_cashier_user->username,
                balance: 100.0,
                password: 'should_be_ignored'
            ));
            $user = $userService->getUser();

            $this->assertSame(UserEditService::RESULT_SUCCESS_IGNORED_PASSWORD, $userService->getResult());
            $this->assertTrue(Hash::check('password', $user->password));
            $this->assertEquals('Ronan Boyle 1', $user->full_name);
            $this->assertEquals(100.0, $user->balance);
        }
    }

    public function testPasswordRemovedIfNewRoleIsNotStaff()
    {
        $this->actingAs($this->_superadmin_user);

        $userService = new UserEditService($this->createRequest(
            id: $this->_cashier_user->id,
            role_id: $this->_camper_role->id,
            full_name: $this->_cashier_user->full_name,
            username: $this->_cashier_user->username,
            password: 'should_be_ignored'
        ));
        $user = $userService->getUser();

        $this->assertSame(UserEditService::RESULT_SUCCESS_APPLIED_PASSWORD, $userService->getResult());
        $this->assertNull($user->password);
    }

    private function createRequest(int $id, ?string $full_name = null, ?string $username = null, float $balance = 0, ?int $role_id = null, ?string $password = null, array $limit = [], array $duration = []): UserRequest
    {
        return new UserRequest([
            'id' => $id,
            'full_name' => $full_name,
            'username' => $username,
            'balance' => $balance,
            'role_id' => $role_id,
            'password' => $password,
            'limit' => $limit,
            'duration' => $duration
        ]);
    }

    private function createRoles(): array
    {
        $superadmin_role = Role::factory()->create();

        $cashier_role = Role::factory()->create([
            'name' => 'Cashier',
            'staff' => true,
            'superuser' => false,
            'order' => 2
        ]);

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 3
        ]);

        return [$superadmin_role, $cashier_role, $camper_role];
    }

    private function createSuperadminUser(Role $superadmin_role): User
    {
        return (new UserCreationService(new UserRequest([
            'full_name' => 'Tadhg Boyle',
            'role_id' => $superadmin_role->id,
            'password' => 'password'
        ])))->getUser();
    }

    private function createCashierUser(Role $cashier_role): User
    {
        return (new UserCreationService(new UserRequest([
            'full_name' => 'Ronan Boyle',
            'role_id' => $cashier_role->id,
            'password' => 'password',
            'balance' => 50.0
        ])))->getUser();
    }
}
