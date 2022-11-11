<?php

namespace Tests\Feature\Activity;

use App\Casts\CategoryType;
use App\Models\Activity;
use App\Models\Category;
use App\Models\Role;
use App\Models\Settings;
use App\Models\User;
use Cknow\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private User $_user;
    private Category $_activities_category;

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

        $this->_user = User::factory()->create([
            'role_id' => Role::factory()->create()->id,
        ]);

        $this->_activities_category = Category::factory()->create([
            'name' => 'Activities',
            'type' => CategoryType::TYPE_ACTIVITIES,
        ]);
    }

    public function testSlotsAvailableReturnsNegativeOneIfUnlimitedSlots(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => true,
        ]);

        $this->assertEquals(-1, $activity->slotsAvailable());
    }

    public function testSlotsAvailableReturnsProperlyIfSlots(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 1,
        ]);

        $this->assertEquals(1, $activity->slotsAvailable());
    }

    public function testSlotsAvailableReturnsFalseIfNoSlotsAvailable(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 1,
        ]);
        $activity->attendants()->attach($this->_user);

        $this->assertEquals(0, $activity->slotsAvailable());
    }

    public function testHasSlotsAvailableReturnsTrueIfUnlimitedSlots(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => true,
        ]);

        $this->assertTrue($activity->hasSlotsAvailable());
    }

    public function testHasSlotsAvailableReturnsTrueIfSlotsAvailable(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 1,
        ]);

        $this->assertTrue($activity->hasSlotsAvailable());
    }

    public function testHasSlotsAvailableReturnsFalseIfNoSlotsAvailable(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 0,
        ]);

        $this->assertFalse($activity->hasSlotsAvailable());
    }

    public function testHasSlotsAvailableReturnsFalseIfNoSlotsAvailableWithCount(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 1,
        ]);

        $this->assertFalse($activity->hasSlotsAvailable(2));
    }

    public function testHasSlotsAvailableReturnsFalseIfSlotsAvailableWhenUsersRegistered(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'unlimited_slots' => false,
            'slots' => 1,
        ]);
        $activity->attendants()->attach($this->_user);

        $this->assertFalse($activity->hasSlotsAvailable());
    }

    public function testGetPriceAfterTaxReturnsProperlyWhenNoPst(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'price' => 100_00,
            'pst' => false,
        ]);

        $this->assertEquals(Money::parse(105_00), $activity->getPriceAfterTax());
    }

    public function testGetPriceAfterTaxReturnsProperlyWhenPst(): void
    {
        $activity = Activity::factory()->create([
            'category_id' => $this->_activities_category->id,
            'price' => 100_00,
            'pst' => true,
        ]);

        $this->assertEquals(Money::parse(112_00), $activity->getPriceAfterTax());
    }
}
