<?php

namespace Tests\Feature\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Services\Users\UserLimits\UserLimitUpsertService;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use App\Helpers\UserLimitsHelper;
use Cknow\Money\Money;
use App\Models\UserLimits;

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
        $this->assertEquals(Money::parse(0_00), UserLimitsHelper::getInfo($user, $candy_category->id)->limit);
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
        $this->assertEquals(Money::parse(25_00), UserLimitsHelper::getInfo($user, $merch_category->id)->limit);
        $this->assertEquals(Money::parse(-1_00), UserLimitsHelper::getInfo($user, $candy_category->id)->limit);
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
        $this->assertSame(UserLimits::LIMIT_DAILY, UserLimitsHelper::getInfo($user, $merch_category->id)->duration_int);
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
                $merch_category->id => UserLimits::LIMIT_WEEKLY,
                $candy_category->id => UserLimits::LIMIT_DAILY
            ]
        ]));

        $this->assertSame(UserLimitUpsertService::RESULT_SUCCESS, $userLimitUpsertService->getResult());
        $this->assertSame(UserLimits::LIMIT_WEEKLY, UserLimitsHelper::getInfo($user, $merch_category->id)->duration_int);
        $this->assertSame(UserLimits::LIMIT_DAILY, UserLimitsHelper::getInfo($user, $candy_category->id)->duration_int);
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