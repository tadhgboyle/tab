<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
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
        if ($this->wouldHaveNonUniqueVariantsIfDeleted($product, $productVariantOption)) {
            return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('error', 'Product variant option cannot be deleted because it would result in non-unique variants.');
        }

        $productVariantOption->delete();

        return redirect()->route('products_view', $product)->with('success', 'Product variant option deleted.');
    }

    private function wouldHaveNonUniqueVariantsIfDeleted(Product $product, ProductVariantOption $productVariantOption): bool
    {
        $variantOptionCombinations = $product->variants->map(function (ProductVariant $variant) use ($productVariantOption) {
            return $variant->optionValueAssignments()
                    ->join('product_variant_options', 'product_variant_option_value_assignments.product_variant_option_id', '=', 'product_variant_options.id')
                    ->join('product_variant_option_values', 'product_variant_option_value_assignments.product_variant_option_value_id', '=', 'product_variant_option_values.id')
                    ->whereNot('product_variant_option_value_assignments.product_variant_option_id', $productVariantOption->id)
                    ->whereNull('product_variant_options.deleted_at')
                    ->whereNull('product_variant_option_values.deleted_at')
                    ->pluck('product_variant_option_value_assignments.product_variant_option_value_id')
                    ->sort();
        });

        return $variantOptionCombinations->count() !== $variantOptionCombinations->unique()->count();
    }
}
