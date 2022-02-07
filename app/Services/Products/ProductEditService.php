<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Service;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\RedirectResponse;

class ProductEditService extends Service
{
    use ProductService;

    public const RESULT_SUCCESS = 0;
    public const RESULT_NOT_EXIST = 1;

    public function __construct(ProductRequest $request)
    {
        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if (!$request->has('stock')) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product = Product::find($request->product_id);

        if ($product === null) {
            $this->_result = self::RESULT_NOT_EXIST;
            $this->_message = 'Product not found with that ID';
            return;
        }

        $product->update([
            'name' => $request->name,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'stock' => $stock,
            'box_size' => $request->box_size ?? -1,
            'unlimited_stock' => $unlimited_stock,
            'stock_override' => $request->has('stock_override'),
            'pst' => $request->has('pst'),
        ]);

        $this->_product = $product;
        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully edited ' . $request->name . '.';
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_list')->with('success', $this->getMessage()),
            self::RESULT_NOT_EXIST => redirect()->route('products_list')->with('error', $this->getMessage()),
        };
    }
}
