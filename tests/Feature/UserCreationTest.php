<?php

namespace Tests\Feature;

use Hash;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Http\Requests\UserRequest;
use App\Services\Users\UserCreationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function testUsernameProperlyFormatted()
    {
        $role_id = Role::factory()->create()->id;

        $userService = new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $role_id
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreationService::RESULT_SUCCESS, $userService->getResult());
        $this->assertEquals('tadhgboyle', $user->username);
    }

    public function testUsernameProperlyFormattedOnDuplicateUsername()
    {
        [, $camper_role] = $this->createRoles();

        new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id
        ));

        $userService = new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreationService::RESULT_SUCCESS, $userService->getResult());
        $this->assertMatchesRegularExpression('/^tadhgboyle(?:[0-9]\d?|100)$/', $user->username);
    }

    public function testHasPasswordWhenRoleIsStaff()
    {
        [$superadmin_role] = $this->createRoles();

        $userService = new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $superadmin_role->id,
            password: 'password'
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreationService::RESULT_SUCCESS, $userService->getResult());
        $this->assertNotEmpty($user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function testDoesNotHavePasswordWhenRoleIsNotStaff()
    {
        [, $camper_role] = $this->createRoles();

        $user = (new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id,
            password: 'password'
        )))->getUser();

        $this->assertEmpty($user->password);
    }

    public function testBalanceIsZeroIfNotSupplied()
    {
        [, $camper_role] = $this->createRoles();

        $user = (new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id,
        )))->getUser();

        $this->assertSame(0.0, $user->balance);
    }

    private function createRequest(?string $full_name = null, ?string $username = null, float $balance = 0, ?int $role_id = null, ?string $password = null, array $limit = [], array $duration = []): UserRequest
    {
        return new UserRequest([
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

        $camper_role = Role::factory()->create([
            'name' => 'Camper',
            'staff' => false,
            'superuser' => false,
            'order' => 2
        ]);

        return [$superadmin_role, $camper_role];
    }

    private function createSuperadminUser(Role $superadmin_role): User
    {
        return (new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $superadmin_role->id
        )))->getUser();
    }
}
