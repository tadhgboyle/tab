<?php

namespace App\Helpers;

use Arr;
use App\Models\Category;
use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Collection;

// TODO: tests
class CategoryHelper extends Helper
{
    private Collection $_categories;
    private Collection $_product_categories;
    private Collection $_activity_categories;

    public function getCategories(): Collection
    {
        return $this->_categories ??= Category::orderBy('name', 'DESC')->get();
    }

    public function getProductCategories(): Collection
    {
        return $this->_product_categories ??= $this->getCategoriesWithType([CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_PRODUCTS]);
    }

    public function getActivityCategories(): Collection
    {
        return $this->_activity_categories ??= $this->getCategoriesWithType([CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_ACTIVITIES]);
    }

    private function getCategoriesWithType(array $types): Collection
    {
        return new Collection(Arr::where($this->getCategories()->all(), function (Category $category) use ($types) {
            return in_array($category->type->id, $types);
        }));
    }
}
