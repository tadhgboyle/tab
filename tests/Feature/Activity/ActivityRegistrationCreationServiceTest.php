<?php

namespace Tests\Feature\Activity;

use App\Casts\CategoryType;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Role;
use App\Models\Rotation;
use App\Models\Settings;
use App\Models\User;
use App\Models\UserLimits;
use App\Services\Activities\ActivityRegistrationCreationService;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class ActivityRegistrationCreationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private Activity $_activity;
    private Category $_activities_category;
    private Rotation $_rotation;

    public function setUp(): void {
        parent::setUp();

        Settings::factory()->createMany([
            [
                'setting' => 'gst',
                'value' => '5.00',
            ],
            [
                'setting' => 'pst',
                'value' => '7.00',
            ],
        ]);

        $this->_rotation = Rotation::factory()->create([
            'name' => 'Rotation',
        ]);

        $this->_user = User::factory()->create([
            'role_id' => Role::factory()->create()->id,
        ]);
        $this->_activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::TYPE_ACTIVITIES,
        ]);
        $this->_activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'price' => 100_00,
            'pst' => true,
        ]);

        $this->actingAs($this->_user);
    }

    public function testCannotRegisterUserIfTheyAreAlreadyAttendingActivity(): void
    {
        $activity = $this->partialMock(Activity::class, function (MockInterface $mock) {
            $mock->shouldReceive('isAttending')
                ->with($this->_user)
                ->andReturnTrue();
        });

        $service = (new ActivityRegistrationCreationService($activity, $this->_user));

        $this->assertEquals(ActivityRegistrationCreationService::RESULT_ALREADY_REGISTERED, $service->getResult());
        $this->assertEquals("Could not register {$this->_user->full_name} for {$activity->name}, they are already attending this activity.", $service->getMessage());
    }

    public function testCannotRegisterUserIfTheActivityDoesNotHaveSlotsAvailable(): void
    {
        $activity = $this->partialMock(Activity::class, function (MockInterface $mock) {
            $mock->shouldReceive('hasSlotsAvailable')
                ->andReturnFalse();
        });

        $service = (new ActivityRegistrationCreationService($activity, $this->_user));

        $this->assertEquals(ActivityRegistrationCreationService::RESULT_OUT_OF_SLOTS, $service->getResult());
        $this->assertEquals("Could not register {$this->_user->full_name} for {$activity->name}, this activity is out of slots.", $service->getMessage());
    }

    public function testCannotRegisterUserIfTheyDontHaveEnoughBalance(): void
    {
        $this->_user->update([
            'balance' => 0_00,
        ]);

        $service = (new ActivityRegistrationCreationService($this->_activity, $this->_user));

        $this->assertEquals(ActivityRegistrationCreationService::RESULT_NO_BALANCE, $service->getResult());
        $this->assertEquals("Could not register {$this->_user->full_name} for {$this->_activity->name}, they do not have enough balance.", $service->getMessage());
    }

    public function testCannotRegisterUserIfTheyDontHaveEnoughCategoryLimitRemaining(): void
    {
        UserLimits::factory()->create([
            'user_id' => $this->_user->id,
            'category_id' => $this->_activities_category->id,
            'limit_per' => 0_00,
            'duration' => UserLimits::LIMIT_WEEKLY,
        ]);

        $service = (new ActivityRegistrationCreationService($this->_activity, $this->_user));

        $this->assertEquals(ActivityRegistrationCreationService::RESULT_OVER_USER_LIMIT, $service->getResult());
        $this->assertEquals("Could not register {$this->_user->full_name} for {$this->_activity->name}, they have reached their limit for the {$this->_activities_category->name} category.", $service->getMessage());
    }

    public function testCanRegisterUserAndStoreRegistration(): void
    {
        $this->_user->update([
            'balance' => 150_00,
        ]);
        $balance_before = $this->_user->balance;

        $service = (new ActivityRegistrationCreationService($this->_activity, $this->_user));

        $this->assertEquals(ActivityRegistrationCreationService::RESULT_SUCCESS, $service->getResult());
        $this->assertEquals("Successfully registered {$this->_user->full_name} for {$this->_activity->name}.", $service->getMessage());

        $registration = $service->getActivityRegistration();
        $this->assertCount(1, $this->_user->activityRegistrations);
        $this->assertEquals($registration->id, $this->_user->activityRegistrations->first()->id);
        $this->assertEquals($this->_activity->id, $registration->activity_id);
        $this->assertEquals(Money::parse(112_00), $registration->total_price);
        $this->assertEquals(Money::parse(100_00), $registration->activity_price);
        $this->assertEquals($balance_before->subtract($registration->total_price), $this->_user->balance);
        $this->assertEquals(5.00, $registration->activity_gst);
        $this->assertEquals(7.00, $registration->activity_pst);
        $this->assertEquals($this->_rotation->id, $registration->rotation_id);
    }
}
