<?php

namespace App\Services\Products;

use App\Models\Product;
use App\Services\Service;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;

class ProductStockAdjustmentService extends Service
{
    use ProductService;

    public const RESULT_INVALID_PRODUCT = 0;
    public const RESULT_INVALID_INPUT = 1;
    public const RESULT_NO_BOX_INPUT = 2;
    public const RESULT_BOX_INPUT_ZERO = 3;
    public const RESULT_SUCCESS = 4;

    // TODO, could we use a productrequest to validate the input?
    public function __construct(Request $request)
    {
        $product_id = $request->product_id;
        $product = Product::find($product_id);

        if ($product === null) {
            $this->_result = self::RESULT_INVALID_PRODUCT;
            $this->_message = 'Invalid product';
            return;
        }

        session()->flash('last_product', $product);

        $validator = Validator::make($request->all(), [
            'adjust_stock' => 'numeric',
            'adjust_box' => 'numeric',
        ]);

        if ($validator->fails()) {
            $this->_result = self::RESULT_INVALID_INPUT;
            $this->_message = implode(', ', $validator->errors()->all());
            return;
        }

        $adjust_stock = (int) $request->adjust_stock;
        $adjust_box = (int) ($request->adjust_box ?? 0);

        if ($adjust_stock === 0) {
            if (!$request->has('adjust_box')) {
                $this->_result = self::RESULT_NO_BOX_INPUT;
                $this->_message = 'Please specify how much stock to add to ' . $product->name . '.';
                return;
            }

            if ($request->adjust_box === 0) {
                $this->_result = self::RESULT_BOX_INPUT_ZERO;
                $this->_message = 'Please specify how many boxes or stock to add to ' . $product->name . '.';
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
            self::RESULT_INVALID_PRODUCT, self::RESULT_NO_BOX_INPUT, self::RESULT_INVALID_INPUT, self::RESULT_BOX_INPUT_ZERO => redirect()->route('products_adjust')->with('error', $this->getMessage()),
            self::RESULT_SUCCESS => redirect()->route('products_adjust')->with('success', $this->getMessage()),
        };
    }
}
