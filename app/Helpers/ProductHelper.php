<?php

namespace App\Helpers;

use App\Models\Product;

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
    public static function deserializeProduct(string $product, bool $full = true): array
    {
        $product_id = strtok($product, '*');

        if ($full) {
            $product_object = Product::findOrFail($product_id);
            $product_name = $product_object->name;
            $product_category = $product_object->category_id;
        }

        $product_quantity = $product_price = $product_gst = $product_pst = $product_returned = 0.00;

        if (preg_match('/\*(.*?)\$/', $product, $match) == 1) {
            $product_quantity = $match[1];
        }

        if (preg_match('/\$(.*?)G/', $product, $match) == 1) {
            $product_price = $match[1];
        }

        if (preg_match('/G(.*?)P/', $product, $match) == 1) {
            $product_gst = $match[1];
        }

        if (preg_match('/P(.*?)R/', $product, $match) == 1) {
            $product_pst = $match[1];
        }

        $product_returned = substr($product, strpos($product, 'R') + 1);

        $return = [
            'id' => $product_id,
            'name' => $product_name ?? '',
            'category' => $product_category ?? '',
            'quantity' => $product_quantity,
            'price' => $product_price,
            'gst' => $product_gst,
            'pst' => $product_pst,
            'returned' => $product_returned,
        ];

        return $return;
    }

}