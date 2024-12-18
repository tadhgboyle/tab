<?php

namespace App\Helpers;

use Cknow\Money\Money;
use App\Models\OrderProduct;

class TaxHelper
{
    // TODO add test
    public static function forOrderProduct(OrderProduct $orderProduct, int $quantity = null): Money
    {
        return self::calculateFor(
            $orderProduct->price,
            $quantity ?? $orderProduct->quantity,
            $orderProduct->pst !== null,
            [
                'pst' => $orderProduct->pst,
                'gst' => $orderProduct->gst,
            ]
        );
    }

    public static function calculateTaxFor(Money $price, int $quantity, bool $apply_pst): Money
    {
        return self::calculateFor($price, $quantity, $apply_pst)->subtract($price->multiply($quantity));
    }

    /**
     * Calculate a price after tax for a specific quantity.
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
