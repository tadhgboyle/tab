<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductVariantRequest;
use App\Models\ProductVariantOptionValueAssignment;

class ProductVariantController extends Controller
{
    public function create(Product $product)
    {
        if ($product->variantOptions->isEmpty()) {
            return redirect()->route('products_view', $product)->with('error', 'Product has no variant options.');
        }

        if ($product->hasAllVariantCombinations()) {
            return redirect()->route('products_view', $product)->with('error', 'Product has all variant combinations already.');
        }

        return view('pages.admin.products.variants.form', [
            'product' => $product,
        ]);
    }

    public function store(ProductVariantRequest $request, Product $product)
    {
        $optionValues = collect($request->input('option_values'));

        if ($existingVariants = $this->isDuplicatedVariant($optionValues)) {
            return back()->with('error', "Product variant already exists for SKUs: {$existingVariants}.")->withInput();
        }

        $productVariant = $product->variants()->create($request->only('sku', 'price', 'stock', 'box_size'));

        $productVariant->optionValueAssignments()->createMany(
            $optionValues->map(function ($optionValueId, $optionId) {
                return [
                    'product_variant_option_id' => $optionId,
                    'product_variant_option_value_id' => $optionValueId
                ];
            })
        );

        return redirect()->route('products_view', $product)->with('success', 'Product variant created.');
    }

    public function edit(Product $product, ProductVariant $productVariant)
    {
        return view('pages.admin.products.variants.form', [
            'product' => $product,
            'productVariant' => $productVariant,
        ]);
    }

    public function update(ProductVariantRequest $request, Product $product, ProductVariant $productVariant)
    {
        $optionValues = collect($request->input('option_values'));

        if ($existingVariants = $this->isDuplicatedVariant($optionValues, $productVariant)) {
            return back()->with('error', "Product variant already exists for SKUs: {$existingVariants}.")->withInput();
        }

        $productVariant->update($request->only('sku', 'price', 'stock', 'box_size'));

        // delete any existing option value assignments that are not in the new list, then add new ones that are not in the existing list
        $productVariant->optionValueAssignments()->whereNotIn('product_variant_option_value_id', $optionValues->values())->delete();
        $productVariant->optionValueAssignments()->createMany(
            $optionValues->diff($productVariant->optionValueAssignments->pluck('product_variant_option_value_id'))
                ->map(function ($optionValueId, $optionId) {
                    return [
                        'product_variant_option_id' => $optionId,
                        'product_variant_option_value_id' => $optionValueId
                    ];
                })
        );

        return redirect()->route('products_view', $product)->with('success', 'Product variant updated.');
    }

    public function destroy(Product $product, ProductVariant $productVariant)
    {
        $productVariant->delete();

        return redirect()->route('products_view', $product)->with('success', 'Product variant deleted.');
    }

    private function isDuplicatedVariant(Collection $optionValues, ?ProductVariant $ignore = null)
    {
        $existingVariants = ProductVariantOptionValueAssignment::whereIn('product_variant_option_value_id', $optionValues->values())
            ->when($ignore, function ($query) use ($ignore) {
                $query->whereNot('product_variant_id', $ignore->id);
            })
            ->where('product_variants.deleted_at', null)
            ->join('product_variants', 'product_variant_option_value_assignments.product_variant_id', '=', 'product_variants.id')
            ->select('product_variants.sku')
            ->groupBy('product_variant_option_value_assignments.product_variant_id')
            ->havingRaw('COUNT(DISTINCT product_variant_option_value_assignments.product_variant_option_value_id) = ?', [count($optionValues->values())])
            ->pluck('product_variants.sku');

        if ($existingVariants->isNotEmpty()) {
            return $existingVariants->implode(', ');
        }

        return null;
    }
}
