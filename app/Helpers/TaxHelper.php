<?php

namespace App\Helpers;

use App\Models\TransactionProduct;
use Cknow\Money\Money;

class TaxHelper
{
    // TODO add test
    public static function forTransactionProduct(TransactionProduct $product, int $quantity): Money
    {
        return self::calculateFor(
            $product->price,
            $quantity,
            $product->pst !== null,
            [
                'pst' => $product->pst,
                'gst' => $product->gst,
            ]
        );
    }

    /**
     * Calculate the tax for a given price and quantity.
     *
     * @param Money $price The base price of the product/activity
     * @param int $quantity Quantity to calculate tax for
     * @param bool $apply_pst Whether to apply PST or not
     * @param array $rates Array of tax rates to use instead of current defaults. Used in returns, so we can use the original tax rates.
     *
     * @return Money The price after tax
     */
    public static function calculateFor(Money $price, int $quantity, bool $apply_pst, array $rates = []): Money
    {
        $settingsHelper = resolve(SettingsHelper::class);

        $gst_percent = $rates['gst'] ?? $settingsHelper->getGst();

        $tax = $gst_percent;
        if ($apply_pst) {
            $tax += $rates['pst'] ?? $settingsHelper->getPst();
        }

        $tax_rate = $tax / 100;

        return $price->multiply($quantity)->multiply(1 + $tax_rate);
    }
}
