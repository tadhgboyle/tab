<?php

namespace Tests\Unit\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Services\Users\UserLimits\UserLimitUpsertService;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use Cknow\Money\Money;
use App\Models\UserLimit;

class UserLimitUpsertServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testNullDataGivesErrorFromUserRequest(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([]));

        $this->assertSame('Limit data is empty', $userLimitUpsertService->getMessage());
        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS_NULL_DATA, $userLimitUpsertService->getResult());
    }

    public function testNegativeLimitGivesErrorFromUserRequest(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $candy_category = Category::factory()->create([
            'name' => 'Candy'
        ]);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([
            'limits' => [
                $candy_category->id => -2_00
            ]
        ]));

        $this->assertSame('Limit must be $-1.00 or above for Candy. ($-1.00 means no limit)', $userLimitUpsertService->getMessage());
        $this->assertSame(UserLimitUpsertService::RESULT_NEGATIVE_LIMIT, $userLimitUpsertService->getResult());
    }

    public function testLimitOfZeroIsAllowed(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $candy_category = Category::factory()->create([
            'name' => 'Candy'
        ]);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([
            'limits' => [
                $candy_category->id => '0'
            ]
        ]));

        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS, $userLimitUpsertService->getResult());
        $this->assertEquals(Money::parse(0_00), $user->limitFor($candy_category)->limit);
    }

    public function testNoLimitProvidedDefaultsToNegativeOneFromUserRequest(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $candy_category = Category::factory()->create([
            'name' => 'Candy'
        ]);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([
            'limits' => [
                $merch_category->id => 25_00,
                $candy_category->id => null
            ]
        ]));

        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS, $userLimitUpsertService->getResult());
        $this->assertEquals(Money::parse(25_00), $user->limitFor($merch_category)->limit);
        $this->assertEquals(Money::parse(-1_00), $user->limitFor($candy_category)->limit);
        $this->assertTrue($user->limitFor($candy_category)->isUnlimited());
    }

    public function testNoDurationProvidedDefaultsToDailyFromUserRequest(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([
            'limits' => [
                $merch_category->id => 25_00,
            ]
        ]));

        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS, $userLimitUpsertService->getResult());
        $this->assertSame(UserLimit::LIMIT_DAILY, $user->limitFor($merch_category)->duration);
        $this->assertSame('day', $user->limitFor($merch_category)->duration());
    }

    public function testDurationIsUsedIfPassed(): void
    {
        [$superadmin_role] = $this->createRoles();

        $user = $this->createSuperadminUser($superadmin_role);

        $this->actingAs($user);

        $merch_category = Category::factory()->create([
            'name' => 'Merch'
        ]);

        $candy_category = Category::factory()->create([
            'name' => 'Candy'
        ]);

        $userLimitUpsertService = new UserLimitUpsertService($user, new UserRequest([
            'limits' => [
                $merch_category->id => 25_00,
                $candy_category->id => 10_00
            ],
            'durations' => [
                $merch_category->id => UserLimit::LIMIT_WEEKLY,
                $candy_category->id => UserLimit::LIMIT_DAILY
            ]
        ]));

        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS, $userLimitUpsertService->getResult());
        $this->assertSame(UserLimit::LIMIT_WEEKLY, $user->limitFor($merch_category)->duration);
        $this->assertSame('week', $user->limitFor($merch_category)->duration());
        $this->assertSame(UserLimit::LIMIT_DAILY, $user->limitFor($candy_category)->duration);
        $this->assertSame('day', $user->limitFor($candy_category)->duration());
    }

    /** @return Role[] */
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
        return User::factory()->create([
            'role_id' => $superadmin_role->id
        ]);
    }    
}