<?php

namespace App\Helpers;

use App\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryHelper
{

    private static CategoryHelper $_instance;

    private Collection $_categories;
    private Collection $_product_categories;
    private Collection $_activity_categories;

    public static function getInstance(): CategoryHelper
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new CategoryHelper();
        }

        return self::$_instance;
    }

    public function getCategories(): Collection
    {
        if (!isset($this->_categories)) {
            $this->_categories = Category::where('deleted', false)->orderBy('name', 'DESC')->get();
        }

        return $this->_categories;
    }

    public function getProductCategories(): Collection
    {
        // Cleaner to use getCategories()->whereIn(), but that doesnt work with the CategoryType cast
        if (!isset($this->_product_categories)) {

            $categories = $this->getCategories();

            $this->_product_categories = new Collection();

            foreach ($categories as $category) {
                if (!in_array($category->type->id, [1, 2])) {
                    continue;
                }

                $this->_product_categories->add($category);
            }
        }

        return $this->_product_categories;
    }

    public function getActivityCategories(): Collection
    {
        if (!isset($this->_activity_categories)) {

            $categories = $this->getCategories();

            $this->_activity_categories = new Collection();

            foreach ($categories as $category) {
                if (!in_array($category->type->id, [1, 3])) {
                    continue;
                }

                $this->_activity_categories->add($category);
            }
        }

        return $this->_activity_categories;
    }
}
