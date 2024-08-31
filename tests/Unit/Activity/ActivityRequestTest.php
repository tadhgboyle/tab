<?php

namespace Tests\Unit\Activity;

use App\Models\Activity;
use App\Models\Category;
use Tests\FormRequestTestCase;
use App\Http\Requests\ActivityRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndIsUnique(): void
    {
        $this->assertHasErrors('name', new ActivityRequest([
            'name' => '',
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

    public function testLocationIsNullable(): void
    {
        $this->assertNotHaveErrors('location', new ActivityRequest([
            'location' => null,
        ]));

        $this->assertNotHaveErrors('location', new ActivityRequest([
            'location' => 'Valid Location',
        ]));
    }

    public function testDescriptionIsNullable(): void
    {
        $this->assertNotHaveErrors('description', new ActivityRequest([
            'description' => null,
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

        $this->assertNotHaveErrors('slots', new ActivityRequest([
            'slots' => -1,
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

    public function testPriceIsRequiredAndNumeric(): void
    {
        $this->assertHasErrors('price', new ActivityRequest([
            'price' => null,
        ]));

        $this->assertHasErrors('price', new ActivityRequest([
            'price' => 'string',
        ]));

        $this->assertNotHaveErrors('price', new ActivityRequest([
            'price' => 5,
        ]));
    }

    public function testStartIsRequiredAndIsDate(): void
    {
        $this->assertHasErrors('start', new ActivityRequest([
            'start' => null,
        ]));

        $this->assertHasErrors('start', new ActivityRequest([
            'start' => 'string',
        ]));

        $this->assertNotHaveErrors('start', new ActivityRequest([
            'start' => now(),
        ]));
    }

    public function testEndIsRequiredAndIsDateAndIsAfterTodayAndAfterStart(): void
    {
        $this->assertHasErrors('end', new ActivityRequest([
            'end' => null,
        ]));

        $this->assertHasErrors('end', new ActivityRequest([
            'end' => 'string',
        ]));

        $this->assertHasErrors('end', new ActivityRequest([
            'start' => now()->subDay(),
        ]));

        $this->assertHasErrors('end', new ActivityRequest([
            'start' => now(),
            'end' => now()->subDay(),
        ]));

        $this->assertNotHaveErrors('end', new ActivityRequest([
            'start' => now(),
            'end' => now()->addDay(),
        ]));
    }

    public function testCategoryIdIsRequiredAndNumericAndInValidValues(): void
    {
        $this->assertHasErrors('category_id', new ActivityRequest([
            'category_id' => null,
        ]));

        $this->assertHasErrors('category_id', new ActivityRequest([
            'category_id' => 'string',
        ]));

        $this->assertHasErrors('category_id', new ActivityRequest([
            'category_id' => 0,
        ]));

        // TODO add test for invalid category id
//        $this->assertNotHaveErrors('category_id', new ActivityRequest([
//            'category_id' => Category::factory()->create([
//                'type' => CategoryType::TYPE_PRODUCTS_ACTIVITIES,
//            ])->id,
//        ]));
    }
}
