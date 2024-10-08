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
use App\Services\Users\UserCreateService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCreateServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        app(RotationSeeder::class)->run();
    }

    public function testUsernameProperlyFormatted(): void
    {
        $role_id = Role::factory()->create()->id;

        $userService = new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $role_id
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreateService::RESULT_SUCCESS, $userService->getResult());
        $this->assertEquals('tadhgboyle', $user->username);
    }

    public function testUsernameProperlyFormattedOnDuplicateUsername(): void
    {
        [, $camper_role] = $this->createRoles();

        new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id
        ));

        $userService = new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreateService::RESULT_SUCCESS, $userService->getResult());
        $this->assertMatchesRegularExpression('/^tadhgboyle(?:[0-9]\d?|100)$/', $user->username);
    }

    public function testHasPassword(): void
    {
        [$superadmin_role] = $this->createRoles();

        $userService = new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $superadmin_role->id,
            password: 'password'
        ));
        $user = $userService->getUser();

        $this->assertSame(UserCreateService::RESULT_SUCCESS, $userService->getResult());
        $this->assertNotEmpty($user->password);
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function testBalanceIsZeroIfNotSupplied(): void
    {
        [, $camper_role] = $this->createRoles();

        $user = (new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id,
        )))->getUser();

        $this->assertEquals(Money::parse(0), $user->balance);
    }

    public function testRotationsAreAttachedToUser(): void
    {
        [, $camper_role] = $this->createRoles();

        $rotations = [
            Rotation::first()->id,
            Rotation::skip(1)->first()->id,
        ];

        $user = (new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $camper_role->id,
            rotations: $rotations,
        )))->getUser();

        $this->assertSame($rotations, $user->rotations->pluck('id')->toArray());
    }

    private function createRequest(
        ?string $full_name = null,
        ?string $username = null,
        float $balance = 0,
        ?int $role_id = null,
        ?string $password = null,
        array $limit = [],
        array $duration = [],
        array $rotations = [],
    ): UserRequest {
        return new UserRequest([
            'full_name' => $full_name,
            'username' => $username,
            'balance' => $balance,
            'role_id' => $role_id,
            'password' => $password,
            'limit' => $limit,
            'duration' => $duration,
            'rotations' => $rotations ?: [Arr::random(Rotation::all()->pluck('id')->all())]
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
        return (new UserCreateService($this->createRequest(
            full_name: 'Tadhg Boyle',
            role_id: $superadmin_role->id
        )))->getUser();
    }
}
