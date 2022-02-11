<?php

namespace Tests\Feature\Activity;

use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\FormRequestTestCase;

class ActivityRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndHasMinAndHasMaxAndIsUnique(): void
    {
        $this->assertHasErrors('name', new ActivityRequest([
            'name' => '',
        ]));

        $this->assertHasErrors('name', new ActivityRequest([
            'name' => '1',
        ]));

        $this->assertHasErrors('name', new ActivityRequest([
            'name' => str_repeat('x', 257),
        ]));

        $this->assertNotHaveErrors('name', new ActivityRequest([
            'name' => 'Activity',
        ]));

        $category = Category::factory()->create();
        $activity = Activity::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertHasErrors('name', new ActivityRequest([
            'name' => $activity->name,
        ]));

        $this->assertNotHaveErrors('name', new ActivityRequest([
            'name' => $activity->name,
            'activity_id' => $activity->id,
        ]));
    }

    public function testLocationIsNullableAndHasMinAndHasMax(): void
    {
        $this->assertNotHaveErrors('location', new ActivityRequest([
            'location' => null,
        ]));

        $this->assertHasErrors('location', new ActivityRequest([
            'location' => '1',
        ]));

        $this->assertHasErrors('location', new ActivityRequest([
            'location' => str_repeat('x', 257),
        ]));

        $this->assertNotHaveErrors('location', new ActivityRequest([
            'location' => 'Valid Location',
        ]));
    }

    public function testDescriptionIsNullableAndHasMinAndHasMax(): void
    {
        $this->assertNotHaveErrors('description', new ActivityRequest([
            'description' => null,
        ]));

        $this->assertHasErrors('description', new ActivityRequest([
            'description' => '1',
        ]));

        $this->assertHasErrors('description', new ActivityRequest([
            'description' => str_repeat('x', 257),
        ]));

        $this->assertNotHaveErrors('description', new ActivityRequest([
            'description' => 'Valid Description',
        ]));
    }

    public function testSlotsAreNullableAndIsNumericAndHasMinAndRequiredUnderCertainConditions(): void
    {
        $this->assertNotHaveErrors('slots', new ActivityRequest([
            'slots' => null,
            'unlimited_slots' => true,
        ]));

        $this->assertHasErrors('slots', new ActivityRequest([
            'slots' => null,
        ]));

        $this->assertHasErrors('slots', new ActivityRequest([
            'slots' => 'string',
        ]));

        $this->assertHasErrors('slots', new ActivityRequest([
            'slots' => 0,
        ]));

        $this->assertNotHaveErrors('slots', new ActivityRequest([
            'slots' => 5,
        ]));
    }
}
