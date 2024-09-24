<?php

namespace Tests\Unit\Admin\Category;

use App\Models\Category;
use App\Enums\CategoryType;
use Tests\FormRequestTestCase;
use App\Http\Requests\CategoryRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryRequestTest extends FormRequestTestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredAndUnique(): void
    {
        $this->assertHasErrors('name', new CategoryRequest([
            'name' => null,
        ]));

        $this->assertNotHaveErrors('name', new CategoryRequest([
            'name' => 'name :D',
        ]));

        $category = Category::factory()->create();

        $this->assertHasErrors('name', new CategoryRequest([
            'name' => $category->name,
        ]));

        $this->assertNotHaveErrors('name', new CategoryRequest([
            'category_id' => $category->id,
            'name' => $category->name,
        ]));
    }

    public function testTypeIsRequiredAndIntegerAndInValidValues(): void
    {
        $this->assertHasErrors('type', new CategoryRequest([
            'type' => null,
        ]));

        $this->assertHasErrors('type', new CategoryRequest([
            'type' => 'string!',
        ]));

        $this->assertHasErrors('type', new CategoryRequest([
            'type' => 4,
        ]));

        $this->assertNotHaveErrors('type', new CategoryRequest([
            'type' => CategoryType::ProductsActivities,
        ]));
    }
}
