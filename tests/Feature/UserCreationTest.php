<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\UserCreationService;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

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
        $role_id = Role::factory()->create()->id;

        $userService1 = new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $role_id
        ));

        $userService2 = new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $role_id
        ));
        $user = $userService2->getUser();

        $this->assertSame(UserCreationService::RESULT_SUCCESS, $userService2->getResult());
        $this->assertMatchesRegularExpression('/^tadhgboyle(?:[1-9]\d?|100)$/', $user->username);
    }

    public function testHasPasswordWhenRoleIsStaff()
    {
        [$superadmin_role, $camper_role] = $this->createCamperAndStaffRoles();

        $user = (new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $superadmin_role->id,
            password: bcrypt('password')
        )))->getUser();
        
        $this->assertNotEmpty($user->password);
    }

    public function testDoesNotHavePasswordWhenRoleIsNotStaff()
    {
        [$superadmin_role, $camper_role] = $this->createCamperAndStaffRoles();

        $user = (new UserCreationService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id,
            password: bcrypt('password')
        )))->getUser();

        $this->assertEmpty($user->password);
    }

    public function testNegativeLimitGivesError()
    {

    }

    private function createRequest(string $full_name = null, string $username = null, float $balance = 0, int $role_id = null, string $password = null, array $limit = []): Request
    {
        return new Request([
            'full_name' => $full_name,
            'username' => $username,
            'balance' => $balance,
            'role_id' => $role_id,
            'password' => $password,
            'limit' => $limit
        ]);
    }

    private function createCamperAndStaffRoles(): array
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
}
