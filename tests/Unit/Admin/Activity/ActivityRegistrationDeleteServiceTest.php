<?php

namespace Tests\Unit\Admin\Activity;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Rotation;
use App\Models\Settings;
use App\Enums\CategoryType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Activities\ActivityRegistrationCreateService;
use App\Services\Activities\ActivityRegistrationDeleteService;

class ActivityRegistrationDeleteServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private Activity $_activity;
    private Category $_activities_category;
    private Rotation $_rotation;

    public function setUp(): void
    {
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
            'balance' => 1000_00,
            'role_id' => Role::factory()->create()->id,
        ]);
        $this->_activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::Activities,
        ]);
        $this->_activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'price' => 100_00,
            'pst' => true,
        ]);

        $this->actingAs($this->_user);
    }

    public function testCannotReturnActivityRegistrationThatIsAlreadyReturned(): void
    {
        $service = new ActivityRegistrationCreateService($this->_activity, $this->_user);

        $this->assertEquals(ActivityRegistrationCreateService::RESULT_SUCCESS, $service->getResult());

        $activityRegistration = $service->getActivityRegistration();
        $activityRegistration->update(['returned' => true]);

        $service = new ActivityRegistrationDeleteService($activityRegistration);

        $this->assertEquals(ActivityRegistrationDeleteService::RESULT_ALREADY_RETURNED, $service->getResult());
        $this->assertEquals("{$this->_user->full_name} has already been removed from this activity.", $service->getMessage());
    }

    public function testCanReturnActivityRegistration(): void
    {
        $user_balance_before = $this->_user->balance;
        $service = new ActivityRegistrationCreateService($this->_activity, $this->_user);

        $this->assertEquals(ActivityRegistrationCreateService::RESULT_SUCCESS, $service->getResult());

        $activityRegistration = $service->getActivityRegistration();

        $this->assertNotEquals($user_balance_before, $this->_user->balance);

        $service = new ActivityRegistrationDeleteService($activityRegistration);

        $this->assertEquals(ActivityRegistrationDeleteService::RESULT_SUCCESS, $service->getResult());
        $this->assertEquals("{$this->_user->full_name} has been removed from the activity and refunded {$activityRegistration->total_price}.", $service->getMessage());
        // $this->assertEquals($user_balance_before->add($activityRegistration->total_price), $this->_user->refresh()->balance);
        $this->assertTrue($activityRegistration->returned);
    }
}
