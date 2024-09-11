<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use Illuminate\Validation\Rule;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionValue;
use App\Http\Controllers\Controller;

class ProductVariantOptionValueController extends Controller
{
    public function store(Request $request, Product $product, ProductVariantOption $productVariantOption)
    {
        $request->validate([
            'value' => [
                'required',
                'string',
                Rule::unique('product_variant_option_values')
                    ->withoutTrashed()
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
                    ->withoutTrashed()
                    ->where('product_variant_option_id', $productVariantOption->id)
                    ->ignoreModel($productVariantOptionValue)
            ],
        ]);

        $productVariantOptionValue->update($request->only('value'));

        return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('success', 'Product variant option value updated.');
    }

    public function destroy(Product $product, ProductVariantOption $productVariantOption, ProductVariantOptionValue $productVariantOptionValue)
    {
        if ($this->wouldHaveNonUniqueVariantsIfDeleted($product, $productVariantOptionValue)) {
            return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('error', 'Product variant option value cannot be deleted because it would result in non-unique variants.');
        }

        $productVariantOptionValue->delete();

        return redirect()->route('products_variant-options_edit', [$product, $productVariantOption])->with('success', 'Product variant option value deleted.');
    }

    private function wouldHaveNonUniqueVariantsIfDeleted(Product $product, ProductVariantOptionValue $productVariantOptionValue): bool
    {
        $variantOptionCombinations = $product->variants->map(function (ProductVariant $variant) use ($productVariantOptionValue) {
            return $variant->optionValueAssignments()
                    ->join('product_variant_options', 'product_variant_option_value_assignments.product_variant_option_id', '=', 'product_variant_options.id')
                    ->join('product_variant_option_values', 'product_variant_option_value_assignments.product_variant_option_value_id', '=', 'product_variant_option_values.id')
                    ->whereNot('product_variant_option_value_assignments.product_variant_option_value_id', $productVariantOptionValue->id)
                    ->whereNull('product_variant_options.deleted_at')
                    ->whereNull('product_variant_option_values.deleted_at')
                    ->pluck('product_variant_option_value_assignments.product_variant_option_value_id')
                    ->sort();
        });

        return $variantOptionCombinations->count() !== $variantOptionCombinations->unique()->count();
    }
}
