<?php

namespace Tests\Feature\Category;

use Tests\TestCase;
use App\Models\Category;
use App\Casts\CategoryType;
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
            'type' => CategoryType::TYPE_PRODUCTS,
        ])->create();

        Category::factory()->count(2)->state([
            'type' => CategoryType::TYPE_ACTIVITIES,
        ])->create();

        Category::factory()->count(3)->state([
            'type' => CategoryType::TYPE_PRODUCTS_ACTIVITIES,
        ])->create();

        $this->assertCount(4, resolve(CategoryHelper::class)->getProductCategories());

        Category::whereIn('type', [CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_PRODUCTS])->each(function (Category $category) {
            $this->assertContains($category->id, resolve(CategoryHelper::class)->getProductCategories()->pluck('id'));
        });
    }

    public function testGetActivityCategoriesWorksAsExpected(): void
    {
        Category::factory()->count(1)->state([
            'type' => CategoryType::TYPE_PRODUCTS,
        ])->create();

        Category::factory()->count(2)->state([
            'type' => CategoryType::TYPE_ACTIVITIES,
        ])->create();

        Category::factory()->count(3)->state([
            'type' => CategoryType::TYPE_PRODUCTS_ACTIVITIES,
        ])->create();

        $this->assertCount(5, resolve(CategoryHelper::class)->getActivityCategories());

        Category::whereIn('type', [CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_ACTIVITIES])->each(function (Category $category) {
            $this->assertContains($category->id, resolve(CategoryHelper::class)->getActivityCategories()->pluck('id'));
        });
    }
}
