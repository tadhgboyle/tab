<?php

namespace App\Helpers;

use App\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryHelper
{

    private static ?CategoryHelper $_instance = null;

    private ?Collection $_categories = null;
    private ?Collection $_product_categories = null;
    private ?Collection $_activity_categories = null;

    public static function getInstance(): CategoryHelper
    {
        if (self::$_instance == null) {
            self::$_instance = new CategoryHelper();
        }

        return self::$_instance;
    }

    public function getCategories(): Collection
    {
        if ($this->_categories == null) {
            $this->_categories = Category::where('deleted', false)->orderBy('name', 'DESC')->get();
        }

        return $this->_categories;
    }

    public function getProductCategories(): Collection
    {
        // TODO: Cleaner to use getCategories()->whereIn(), but that doesnt work with the CategoryType cast
        if ($this->_product_categories == null) {
            $categories = $this->getCategories();
            $this->_product_categories = new Collection();

            foreach ($categories as $category) {
                if (!in_array($category->type->id, [1, 2])) {
                    continue;
                }

                $this->_product_categories[] = $category;
            }
        }

        return $this->_product_categories;
    }

    public function getActivityCategories(): Collection
    {
        if ($this->_activity_categories == null) {
            $categories = $this->getCategories();
            $this->_activity_categories = new Collection();

            foreach ($categories as $category) {
                if (!in_array($category->type->id, [1, 3])) {
                    continue;
                }

                $this->_activity_categories[] = $category;
            }
        }

        return $this->_activity_categories;
    }
}
