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
            'name' => ['required', 'string', Rule::unique('product_variant_options')->where('product_id', $product->id)],
            'values' => ['required', 'string'],
        ]);

        $productVariantOption = $product->variantOptions()->create($request->only('name'));

        $values = collect(explode(',', $request->values));
        $productVariantOption->values()->createMany(
            $values->map(fn ($value) => ['value' => $value])->all()
        );

        return redirect()->route('products_view', $product)->with('success', 'Product variant option created successfully.');
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
            'name' => ['required', 'string', Rule::unique('product_variant_options')->where('product_id', $product->id)->ignore($productVariantOption)],
            'values' => ['required', 'string'],
        ]);

        $productVariantOption->update($request->only('name'));

        $values = collect(explode(',', $request->values));
        $existingValues = $productVariantOption->values->pluck('value')->all();
        $newValues = $values->diff($existingValues);
        $productVariantOption->values()->createMany(
            $newValues->map(fn ($value) => ['value' => $value])->all()
        );
        $productVariantOption->values()->whereNotIn('value', $values)->delete();

        return redirect()->route('products_view', $product)->with('success', 'Product variant option updated successfully.');
    }
}
