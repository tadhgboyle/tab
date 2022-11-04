<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Service;
use Illuminate\Http\RedirectResponse;

class ProductDeleteService extends Service
{
    use ProductService;

    public const RESULT_SUCCESS = 0;

    public function __construct(Product $product)
    {
        $product->delete();

        $this->_product = $product;
        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully deleted ' . $product->name . '.';
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_list')->with('success', $this->getMessage()),
            default => redirect()->route('products_list')->with('error', $this->getMessage()),
        };
    }
}
