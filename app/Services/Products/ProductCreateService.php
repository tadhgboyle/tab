<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\HttpService;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\RedirectResponse;

class ProductCreateService extends HttpService
{
    use ProductService;

    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(ProductRequest $request)
    {
        $unlimited_stock = $request->has('unlimited_stock');

        $stock = 0;
        if ($request->stock === null) {
            $unlimited_stock = true;
        } else {
            $stock = $request->stock;
        }

        $product = new Product();
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->status = $request->status;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->category_id = $request->category_id;
        $product->stock = $stock;
        $product->box_size = $request->box_size ?? -1;
        $product->unlimited_stock = $unlimited_stock;
        $product->stock_override = $request->has('stock_override');
        $product->pst = $request->has('pst');
        $product->restore_stock_on_return = $request->has('restore_stock_on_return');
        $product->save();

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Created {$product->name}";
        $this->_product = $product;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_view', $this->getProduct())->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', 'Error creating product')
        };
    }
}
