<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\HttpService;
use App\Models\ProductVariant;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProductStockAdjustmentRequest;

class ProductStockAdjustmentService extends HttpService
{
    use ProductService;

    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(ProductStockAdjustmentRequest $request, Product $product, ?ProductVariant $productVariant = null)
    {
        session()->flash('last_product', $product);
        if ($productVariant?->exists) {
            session()->flash('last_product_variant', $productVariant);
        }

        $adjust_stock = (int) $request->adjust_stock;

        if ($productVariant?->exists) {
            $name = $productVariant->description();
        } else {
            $name = $product->name;
        }

        if ($productVariant?->exists) {
            $productVariant->adjustStock($adjust_stock);
        } else {
            $product->adjustStock($adjust_stock);
        }

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully added ' . $adjust_stock . ' stock to ' . $name . '.';
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_ledger')->with('success', $this->getMessage()),
            default => redirect()->route('products_ledger')->with('error', $this->getMessage()),
        };
    }
}
