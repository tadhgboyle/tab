<?php

namespace App\Helpers;

use stdClass;
use App\Models\User;
use App\Models\Product;
use App\Models\UserLimits;
use Illuminate\Support\Carbon;
use App\Http\Controllers\TransactionController;
use App\Models\Activity;

// TODO: Move these to user model. $user->canSpendInCategory($cat_id, 5.99)
class UserLimitsHelper
{
    public static function canSpend(User $user, float $spending, int $category_id): bool
    {
        $info = self::getInfo($user->id, $category_id);

        if ($info->limit_per == -1) {
            return true;
        }

        $spent = self::findSpent($user, $category_id, $info);

        return !(($spent + $spending) > $info->limit_per);
    }

    // TODO implement
    public static function getRemaining(User $user, int $category_id): float
    {
        $remaining = 0.00;

        $info = self::getInfo($user->id, $category_id);

        if ($info->limit_per == -1) {
            $remaining = -1;
        } else {
            $spent = self::findSpent($user, $category_id, $info);

            $remaining = $info->limit_per - $spent;
        }

        return number_format($remaining, 2);
    }

    // TODO clean
    public static function getInfo(?int $user_id, int $category_id): stdClass
    {
        $info = $user_id == null ? [] : UserLimits::where([['user_id', $user_id], ['category_id', $category_id]])->select('duration', 'limit_per')->get();
        if (count($info)) {
            $info = $info[0];
        }

        $return = new stdClass();
        if (isset($info->duration)) {
            $return->duration = $info->duration == 0 ? 'day' : 'week';
        } else {
            $return->duration = 'week';
        }
        if (isset($info->limit_per)) {
            $return->limit_per = $info->limit_per;
        } else {
            // Usually this will happen when we make a new category after a user was made
            $return->limit_per = -1;
        }

        return $return;
    }

    public static function findSpent(User $user, string $category_id, object $info): float
    {
        // First, if they have unlimited money for this category, let's grab all their transactions
        if ($info->limit_per == -1) {
            $transactions = $user->getTransactions();
            $activity_transactions = $user->getActivityTransactions();
        } else {
            // Determine how far back to grab transactions from
            $transactions = $user->getTransactions()->where('created_at', '>=', Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString());
            $activity_transactions = $user->getActivityTransactions()->where('created_at', '>=', Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString());
        }

        $category_spent = 0.00;

        // Loop applicable transactions, then do a bunch of wacky shit
        foreach ($transactions as $transaction) {
            if ($transaction->status) {
                continue;
            }
            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            $transaction_products = explode(', ', $transaction['products']);
            foreach ($transaction_products as $transaction_product) {
                if ($category_id == Product::find(strtok($transaction_product, '*'))->category_id) {
                    $item_info = TransactionController::deserializeProduct($transaction_product, false);
                    $tax_percent = $item_info['gst'];
                    if ($item_info['pst'] != 'null') {
                        $tax_percent += $item_info['pst'] - 1;
                    }
                    $quantity_available = $item_info['quantity'] - $item_info['returned'];
                    $category_spent += ($item_info['price'] * $quantity_available) * $tax_percent;
                }
            }
        }

        foreach ($activity_transactions as $activity_transaction) {
            if ($activity_transaction->status) {
                continue;
            }

            $activity = Activity::find($activity_transaction->activity_id);
            if ($activity->category_id != $category_id) {
                continue;
            }

            $category_spent += $activity->getPrice();
        }

        return number_format($category_spent, 2);
    }
}
