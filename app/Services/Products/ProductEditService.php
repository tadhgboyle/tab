<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\HttpService;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\RedirectResponse;

class ProductEditService extends HttpService
{
    use ProductService;

    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(ProductRequest $request, Product $product)
    {
        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if (!$request->has('stock')) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product->update([
            'name' => $request->name,
            'sku' => $request->sku,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'stock' => $stock,
            'box_size' => $request->box_size ?? -1,
            'unlimited_stock' => $unlimited_stock,
            'stock_override' => $request->has('stock_override'),
            'pst' => $request->has('pst'),
            'restore_stock_on_return' => $request->has('restore_stock_on_return'),
        ]);

        $this->_product = $product;
        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Successfully edited {$product->name}.";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_view', $this->_product)->with('success', $this->getMessage()),
            default => redirect()->back()->withInput()->with('error', $this->getMessage()),
        };
    }
}
