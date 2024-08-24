<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ProductVariantOption;

class ProductVariantOptionController extends Controller
{
    public function create(Product $product)
    {
        return view('pages.products.variant-options.form', [
            'product' => $product,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('product_variant_options')
                    ->withoutTrashed()
                    ->where('product_id', $product->id)
            ],
        ]);

        $variantOption = $product->variantOptions()->create($request->only('name'));

        return redirect()->route('products_variant-options_edit', [$product, $variantOption])->with('success', 'Product variant option created, you can now add values.');
    }

    public function edit(Product $product, ProductVariantOption $productVariantOption)
    {
        return view('pages.products.variant-options.form', [
            'product' => $product,
            'productVariantOption' => $productVariantOption,
        ]);
    }

    public function update(Request $request, Product $product, ProductVariantOption $productVariantOption)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('product_variant_options')
                    ->withoutTrashed()
                    ->where('product_id', $product->id)
                    ->ignoreModel($productVariantOption)
            ],
        ]);

        $productVariantOption->update($request->only('name'));

        return redirect()->route('products_view', $product)->with('success', 'Product variant option updated.');
    }

    public function destroy(Product $product, ProductVariantOption $productVariantOption)
    {
        $productVariantOption->delete();

        return redirect()->route('products_view', $product)->with('success', 'Product variant option deleted.');
    }
}
