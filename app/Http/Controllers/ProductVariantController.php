<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantOptionValueAssignment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductVariantController extends Controller
{
    public function create(Product $product)
    {
        return view('pages.products.variants.form', [
            'product' => $product,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'sku' => ['required', 'string', Rule::unique('product_variants')],
            'price' => ['required', 'numeric'],
            // options validation:
            // - at least one option selected
            // - all selected options are valid for the product
            // - the option combination is unique for the products variants
            'option_values' => ['required', 'array'],
            'option_values.*' => ['required', Rule::exists('product_variant_option_values', 'id')->where('product_variant_option_id', $product->variantOptions->pluck('id'))],
        ]);

        $optionValues = collect($request->input('option_values'));

        // ensure the option value combination is unique
        $existingVariants = ProductVariantOptionValueAssignment::whereIn('product_variant_option_value_id', $optionValues->values())
            ->join('product_variants', 'product_variant_option_value_assignments.product_variant_id', '=', 'product_variants.id')
            ->select('product_variants.sku')
            ->groupBy('product_variant_option_value_assignments.product_variant_id')
            ->havingRaw('COUNT(DISTINCT product_variant_option_value_assignments.product_variant_option_value_id) = ?', [count($optionValues->values())])
            ->pluck('product_variants.sku');

        if ($existingVariants->isNotEmpty()) {
            return back()->with('error', "Product variant already exists for SKUs: {$existingVariants->join(', ')}.")->withInput();
        }

        $productVariant = $product->variants()->create($request->only('sku', 'price'));

        $productVariant->optionValueAssignments()->createMany(
            $optionValues->map(function ($optionValueId, $optionId) {
                return [
                    'product_variant_option_id' => $optionId,
                    'product_variant_option_value_id' => (int) $optionValueId
                ];
            })
        );

        return redirect()->route('products_view', $product)->with('success', 'Product variant created.');
    }

    public function edit(Product $product, ProductVariant $productVariant)
    {
        return view('pages.products.variants.form', [
            'product' => $product,
            'productVariant' => $productVariant,
        ]);
    }
}
