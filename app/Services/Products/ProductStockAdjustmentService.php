<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\HttpService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\ProductStockAdjustmentRequest;

class ProductStockAdjustmentService extends HttpService
{
    use ProductService;

    public const RESULT_NO_BOX_INPUT = 'NO_BOX_INPUT';
    public const RESULT_BOTH_INPUT_ZERO = 'BOTH_INPUT_ZERO';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(ProductStockAdjustmentRequest $request, Product $product)
    {
        session()->flash('last_product', $product);

        $adjust_stock = (int) $request->adjust_stock;
        $adjust_box = (int) ($request->adjust_box ?? 0);

        if ($adjust_stock === 0) {
            if ($product->box_size !== -1 && !$request->has('adjust_box')) {
                $this->_result = self::RESULT_NO_BOX_INPUT;
                $this->_message = 'Please specify how much stock or boxes to add to ' . $product->name . '.';
                return;
            }

            if ($adjust_box === 0) {
                $this->_result = self::RESULT_BOTH_INPUT_ZERO;
                $this->_message = 'Please specify how much stock to add to ' . $product->name . '.';
                return;
            }
        }

        $product->adjustStock($adjust_stock);

        if ($request->has('adjust_box')) {
            $product->addBox($adjust_box);
        }

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = 'Successfully added ' . $adjust_stock . ' stock and ' . $adjust_box . ' boxes to ' . $product->name . '.';
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('products_ledger')->with('success', $this->getMessage()),
            default => redirect()->route('products_ledger')->with('error', $this->getMessage()),
        };
    }
}
