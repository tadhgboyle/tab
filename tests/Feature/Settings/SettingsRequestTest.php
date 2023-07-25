<?php

namespace Tests\Feature\Settings;

use App\Models\Activity;
use App\Models\Category;
use App\Casts\CategoryType;
use Tests\FormRequestTestCase;
use App\Http\Requests\ActivityRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingsRequestTest extends FormRequestTestCase
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
}
