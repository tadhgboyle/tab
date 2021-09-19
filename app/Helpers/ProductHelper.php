<?php

namespace App\Helpers;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductHelper
{
    /**
     * Example deserialized input:
     * ID: 34
     * Quantity: 2
     * Price: 1.45 each
     * GST: 1.08
     * PST: 1.04
     * Returned: 1.
     *
     * Example Output: 34*2$1.45G1.08P1.04R1
     */
    public static function serializeProduct($id, $quantity, $price, $gst, $pst, $returned): string
    {
        return $id . '*' . $quantity . '$' . $price . 'G' . $gst . 'P' . $pst . 'R' . $returned;
    }

    /**
     * Example serialized product:
     * 3*5$1.45G1.07P1.05R0
     * ID: 3
     * Quantity: 5
     * Price: 1.45 each
     * Gst: 1.07
     * Pst: 1.05
     * Returned: Quantity returned -- Default 0.
     */
    public static function deserializeProduct(string $serializedProduct, bool $full = true): array
    {
        $productId = Str::before($serializedProduct, '*');

        if ($full) {
            $product = Product::findOrFail($productId);
            $productName = $product->name;
            $productCategory = $product->category_id;
        }

        $productQuantity = Str::between($serializedProduct, '*', '$');

        $productPrice = Str::between($serializedProduct, '$', 'G');

        $productGst = Str::between($serializedProduct, 'G', 'P');

        $productPst = Str::between($serializedProduct, 'P', 'R');

        $productReturned = Str::after($serializedProduct, 'R');

        return [
            'id' => $productId,
            'name' => $productName ?? '',
            'category' => $productCategory ?? '',
            'quantity' => $productQuantity,
            'price' => $productPrice,
            'gst' => $productGst,
            'pst' => $productPst,
            'returned' => $productReturned,
        ];
    }
}
