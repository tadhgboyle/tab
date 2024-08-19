<?php

namespace App\Helpers;

use App\Models\Category;
use App\Enums\CategoryType;
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
        return $this->productCategories ??= $this->getCategoriesOfType(CategoryType::Products);
    }

    /** @return Collection<int, Category> */
    public function getActivityCategories(): Collection
    {
        return $this->activityCategories ??= $this->getCategoriesOfType(CategoryType::Activities);
    }

    private function getCategoriesOfType(CategoryType $type): Collection
    {
        return $this->getCategories()->filter(static function (Category $category) use ($type) {
            return in_array($category->type, [$type, CategoryType::ProductsActivities], true);
        });
    }
}
