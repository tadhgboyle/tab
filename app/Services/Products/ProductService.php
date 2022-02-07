<?php

namespace App\Services\Products;

use App\Models\Product;

trait ProductService
{
    protected Product $_product;

    final public function getProduct(): Product
    {
        return $this->_product;
    }
}
