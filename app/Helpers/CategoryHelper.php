<?php

namespace App\Helpers;

use Arr;
use App\Models\Category;
use App\Casts\CategoryType;
use Illuminate\Database\Eloquent\Collection;

class CategoryHelper extends Helper
{
    private Collection $_categories;
    private Collection $_product_categories;
    private Collection $_activity_categories;

    public function getCategories(): Collection
    {
        if (!isset($this->_categories)) {
            $this->_categories = Category::where('deleted', false)->orderBy('name', 'DESC')->get();
        }

        return $this->_categories;
    }

    public function getProductCategories(): Collection
    {
        if (!isset($this->_product_categories)) {
            $this->_product_categories = $this->getCategoriesWithType([CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_PRODUCTS]);
        }

        return $this->_product_categories;
    }

    public function getActivityCategories(): Collection
    {
        if (!isset($this->_activity_categories)) {
            $this->_activity_categories = $this->getCategoriesWithType([CategoryType::TYPE_PRODUCTS_ACTIVITIES, CategoryType::TYPE_ACTIVITIES]);
        }

        return $this->_activity_categories;
    }

    private function getCategoriesWithType(array $types): Collection
    {
        return new Collection(Arr::where($this->getCategories()->all(), function (Category $category) use ($types) {
            return in_array($category->type->id, $types);
        }));
    }
}
