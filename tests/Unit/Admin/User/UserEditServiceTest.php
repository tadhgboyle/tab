<?php

namespace Tests\Unit\Admin\User;

use Arr;
use Hash;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\Rotation;
use App\Http\Requests\UserRequest;
use Database\Seeders\RotationSeeder;
use App\Services\Users\UserEditService;
use App\Services\Users\UserCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

// TODO test that rotations are attached/detached properly
class UserEditServiceTest extends TestCase
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

    public function testCannotEditUsersWithHigherRoleThanSelf(): void
    {
        $this->actingAs($this->_cashier_user);

        $userService = new UserEditService($this->createRequest(
            user_id: $this->_superadmin_user->id,
            role_id: $this->_superadmin_role->id
        ), $this->_superadmin_user);

        $this->assertSame(UserEditService::RESULT_CANT_MANAGE_THAT_ROLE, $userService->getResult());
    }

    public function testUpdatesAttributesProperly(): void
    {
        $this->actingAs($this->_superadmin_user);

        foreach ([$this->_cashier_role, $this->_superadmin_role] as $role) {
            $userService = new UserEditService($this->createRequest(
                user_id: $this->_cashier_user->id,
                full_name: 'Ronan Boyle 1',
                username: $this->_cashier_user->username,
                balance: 100,
                role_id: $role->id,
                password: 'new_password!'
            ), $this->_cashier_user);

            $this->assertSame(UserEditService::RESULT_SUCCESS, $userService->getResult());
            $this->assertTrue(Hash::check('new_password!', $userService->getUser()->password));
            $this->assertEquals('Ronan Boyle 1', $userService->getUser()->full_name);
            $this->assertEquals(Money::parse(100_00), $userService->getUser()->balance);
        }
    }

    public function testPasswordNotChangedIfPasswordNotPassed(): void
    {
        $this->actingAs($this->_superadmin_user);

        $passwordBefore = $this->_cashier_user->password;

        $userService = new UserEditService($this->createRequest(
            user_id: $this->_cashier_user->id,
            full_name: $this->_cashier_user->full_name,
            username: $this->_cashier_user->username,
            role_id: $this->_camper_role->id,
        ), $this->_cashier_user);

        $this->assertSame(UserEditService::RESULT_SUCCESS, $userService->getResult());
        $this->assertEquals($passwordBefore, $userService->getUser()->password);
    }

    private function createRequest(int $user_id, ?string $full_name = null, ?string $username = null, float $balance = 0, ?int $role_id = null, ?string $password = null, array $limit = [], array $duration = []): UserRequest
    {
        return new UserRequest([
            'user_id' => $user_id,
            'full_name' => $full_name,
            'username' => $username,
            'balance' => $balance,
            'role_id' => $role_id,
            'password' => $password,
            'limits' => $limit,
            'durations' => $duration,
            'rotations' => [Arr::random(Rotation::all()->pluck('id')->all())]
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
        app(RotationSeeder::class)->run();

        return (new UserCreateService(new UserRequest([
            'full_name' => 'Tadhg Boyle',
            'role_id' => $superadmin_role->id,
            'password' => 'password',
            'rotations' => [Arr::random(Rotation::all()->pluck('id')->all())]
        ])))->getUser();
    }

    private function createCashierUser(Role $cashier_role): User
    {
        return (new UserCreateService(new UserRequest([
            'full_name' => 'Ronan Boyle',
            'role_id' => $cashier_role->id,
            'password' => 'password',
            'balance' => 50.0,
            'rotations' => [Arr::random(Rotation::all()->pluck('id')->all())]
        ])))->getUser();
    }
}
