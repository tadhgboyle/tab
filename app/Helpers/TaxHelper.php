<?php

namespace App\Helpers;

class TaxHelper {

    /**
     * Calculate the tax for a given price and quantity.
     *
     * @param float $price The base price of the product/activity
     * @param int $quantity Quantity to calculate tax for
     * @param bool $apply_pst Whether to apply PST or not
     * @param array $rates Array of tax rates to use instead of current defaults. Used in returns, so we can use the original tax rates.
     * @return float The price after tax
     */
    public static function calculateFor(float $price, int $quantity, bool $apply_pst, array $rates = []): float {
        $settingsHelper = resolve(SettingsHelper::class);

        $default_rates = [
            'pst' => $settingsHelper->getPst(),
            'gst' => $settingsHelper->getGst(),
        ];

        $gst_percent = $rates['gst'] ?? $default_rates['gst'];
        $pst_percent = $rates['pst'] ?? $default_rates['pst'];

        $tax = $gst_percent;
        if ($apply_pst) {
            $tax += $pst_percent;
        }

        $tax_rate = $tax / 100;
        return round($price * $quantity * (1 + $tax_rate), 2);
    }
}
