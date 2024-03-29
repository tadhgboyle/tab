<?php

namespace App\Helpers;

use App\Models\Category;
use App\Casts\CategoryType;
use Illuminate\Support\Collection;

class CategoryHelper
{
    private Collection $categories;
    private Collection $productCategories;
    private Collection $activityCategories;

    /** @return Collection<int, Category> */
    public function getCategories(): Collection
    {
        return $this->categories ??= Category::query()->orderBy('name', 'ASC')->get();
    }

    /** @return Collection<int, Category> */
    public function getProductCategories(): Collection
    {
        return $this->productCategories ??= $this->getCategoriesOfType(CategoryType::TYPE_PRODUCTS);
    }

    /** @return Collection<int, Category> */
    public function getActivityCategories(): Collection
    {
        return $this->activityCategories ??= $this->getCategoriesOfType(CategoryType::TYPE_ACTIVITIES);
    }

    private function getCategoriesOfType(int $type): Collection
    {
        return $this->getCategories()->filter(static function (Category $category) use ($type) {
            return in_array($category->type->id, [$type, CategoryType::TYPE_PRODUCTS_ACTIVITIES], true);
        });
    }
}
