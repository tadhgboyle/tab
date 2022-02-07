<?php

namespace App\Services\Products;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Services\Service;
use Illuminate\Http\RedirectResponse;

class ProductCreationService extends Service
{
    use ProductService;

    public const RESULT_SUCCESS = 0;

    public function __construct(ProductRequest $request) {
        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if ($request->stock === null) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->category_id = $request->category_id;
        $product->stock = $stock;
        $product->box_size = $request->box_size ?? -1;
        $product->unlimited_stock = $unlimited_stock;
        $product->stock_override = $request->has('stock_override');
        $product->pst = $request->has('pst');
        $product->save();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Successfully created {$product->name}";
        $this->_product = $product;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_list')->with('success', $this->getMessage()),
            default => redirect()->back()->withInput()->with('error', 'Error creating product')
        };
    }
}
