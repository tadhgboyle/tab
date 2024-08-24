<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariantOptionValue;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ProductVariantOption;

class ProductVariantOptionValueController extends Controller
{
    public function store(Request $request, Product $product, ProductVariantOption $productVariantOption)
    {
        $request->validate([
            'value' => [
                'required',
                'string',
                Rule::unique('product_variant_option_values')
                    // ->withoutTrashed()
                    ->where('product_variant_option_id', $productVariantOption->id)
            ],
        ]);

        $productVariantOption->values()->create($request->only('value'));

        return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('success', 'Product variant option value added.');
    }

    public function update(Request $request, Product $product, ProductVariantOption $productVariantOption, ProductVariantOptionValue $productVariantOptionValue)
    {
        $request->validate([
            'value' => [
                'required',
                'string',
                Rule::unique('product_variant_option_values')
                    // ->withoutTrashed()
                    ->where('product_variant_option_id', $productVariantOption->id)
                    ->ignoreModel($productVariantOptionValue)
            ],
        ]);

        $productVariantOptionValue->update($request->only('value'));

        return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('success', 'Product variant option value updated.');
    }

    public function destroy(Product $product, ProductVariantOption $productVariantOption, ProductVariantOptionValue $productVariantOptionValue)
    {
        $productVariantOptionValue->delete();

        return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('success', 'Product variant option value deleted.');
    }
}
