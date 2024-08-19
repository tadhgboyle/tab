<?php

namespace Tests\Unit\Category;

use Tests\TestCase;
use App\Models\Category;
use App\Enums\CategoryType;
use App\Helpers\CategoryHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryHelperTest extends TestCase
{
    use RefreshDatabase;

    public function testGetCategoriesWorksAsExpected(): void
    {
        Category::factory()->count(6)->create();

        $this->assertCount(6, resolve(CategoryHelper::class)->getCategories());
    }

    public function testGetProductCategoriesWorksAsExpected(): void
    {
        Category::factory()->count(1)->state([
            'type' => CategoryType::Products,
        ])->create();

        Category::factory()->count(2)->state([
            'type' => CategoryType::Activities,
        ])->create();

        Category::factory()->count(3)->state([
            'type' => CategoryType::ProductsActivities,
        ])->create();

        $this->assertCount(4, resolve(CategoryHelper::class)->getProductCategories());

        Category::whereIn('type', [CategoryType::ProductsActivities, CategoryType::Products])->each(function (Category $category) {
            $this->assertContains($category->id, resolve(CategoryHelper::class)->getProductCategories()->pluck('id'));
        });
    }

    public function testGetActivityCategoriesWorksAsExpected(): void
    {
        Category::factory()->count(1)->state([
            'type' => CategoryType::Products,
        ])->create();

        Category::factory()->count(2)->state([
            'type' => CategoryType::Activities,
        ])->create();

        Category::factory()->count(3)->state([
            'type' => CategoryType::ProductsActivities,
        ])->create();

        $this->assertCount(5, resolve(CategoryHelper::class)->getActivityCategories());

        Category::whereIn('type', [CategoryType::ProductsActivities, CategoryType::Activities])->each(function (Category $category) {
            $this->assertContains($category->id, resolve(CategoryHelper::class)->getActivityCategories()->pluck('id'));
        });
    }
}
