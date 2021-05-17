<?php

namespace App\Helpers;

use stdClass;
use App\Models\User;
use App\Models\Product;
use App\Models\Activity;
use App\Models\UserLimits;
use Illuminate\Support\Carbon;
use App\Http\Controllers\TransactionController;

// TODO: Move these to user model. $user->canSpendInCategory($cat_id, 5.99)
class UserLimitsHelper
{
    public static function canSpend(User $user, float $spending, int $category_id, ?object $info = null): bool
    {
        if ($info == null) {
            $info = self::getInfo($user, $category_id);
        }

        if ($info->limit_per == -1) {
            return true;
        }

        $spent = self::findSpent($user, $category_id, $info);

        return !(($spent + $spending) > $info->limit_per);
    }

    public static function getInfo(User $user, int $category_id): stdClass
    {
        $info = UserLimits::where([['user_id', $user->id], ['category_id', $category_id]])->select('duration', 'limit_per')->get();

        $limit_info = new stdClass();

        if ($info->count()) {
            $info = $info->first();
            $limit_info->duration = $info->duration == 0 ? 'day' : 'week';
            $limit_info->limit_per = $info->limit_per;
        } else {
            $limit_info->duration = 'week';
            $limit_info->limit_per = -1;
        }

        return $limit_info;
    }

    public static function findSpent(User $user, int $category_id, object $info): float
    {
        // If they have unlimited money for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($info->limit_per == -1) {
            $transactions = $user->getTransactions()->where('status', false);
            $activity_transactions = $user->getActivityTransactions('status', false);
        } else {
            $transactions = $user->getTransactions()->where('created_at', '>=', Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString())->where('status', false);
            $activity_transactions = $user->getActivityTransactions()->where('created_at', '>=', Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString())->where('status', false);
        }

        $category_spent = 0.00;

        foreach ($transactions as $transaction) {

            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            $transaction_products = explode(', ', $transaction['products']);

            foreach ($transaction_products as $transaction_product) {
                $product = Product::find(strtok($transaction_product, '*'));
                if ($product->category_id != $category_id) {
                    continue;
                }

                $item_info = TransactionController::deserializeProduct($transaction_product, false);

                $tax_percent = $item_info['gst'];

                if ($item_info['pst'] != 'null') {
                    $tax_percent += $item_info['pst'] - 1;
                }

                $quantity_available = $item_info['quantity'] - $item_info['returned'];

                $category_spent += ($item_info['price'] * $quantity_available) * $tax_percent;
            }
        }

        foreach ($activity_transactions as $activity_transaction) {
            $activity = Activity::find($activity_transaction->activity_id);
            if ($activity->category_id != $category_id) {
                continue;
            }

            $category_spent += $activity->getPrice();
        }

        return number_format($category_spent, 2);
    }
}
