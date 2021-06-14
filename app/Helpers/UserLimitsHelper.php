<?php

namespace App\Helpers;

use stdClass;
use App\Models\User;
use App\Models\Product;
use App\Models\Activity;
use App\Models\Category;
use App\Models\UserLimits;
use App\Services\Users\UserEditService;
use Illuminate\Support\Carbon;
use App\Http\Requests\UserRequest;
use App\Services\Users\UserCreationService;

// TODO: Move these to user model. $user->canSpendInCategory($cat_id, 5.99)
class UserLimitsHelper
{
    public static function createOrEditFromRequest(UserRequest $request, User $user, string $class): array
    {
        if ($request->limit == null) {
            return [null, null];
        }

        foreach ($request->limit as $category_id => $limit) {

            // Default to limit per day rather than week if not specified
            $duration = $request->duration[$category_id] ?? UserLimits::LIMIT_DAILY;

            // Default to -1 if limit not typed in
            if ($limit == null || !isset($limit) || empty($limit)) {
                $limit = -1;
            }

            if ($limit < -1) {
                $message = 'Limit must be -1 or above for ' . Category::find($category_id)->name . '. (-1 means no limit)';
                $result = ($class == UserCreationService::class) 
                            ? UserCreationService::RESULT_INVALID_LIMIT 
                            : UserEditService::RESULT_INVALID_LIMIT;
                return [$message, $result];
            }

            UserLimits::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category_id,
                ],
                [
                    'limit_per' => $limit,
                    'duration' => $duration,
                    'editor_id' => auth()->id()
                ]
            );
        }

        return [null, null];
    }

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
            $limit_info->duration = $info->duration == UserLimits::LIMIT_DAILY ? 'day' : 'week';
            $limit_info->duration_int = (int) $info->duration;
            $limit_info->limit_per = $info->limit_per;
        } else {
            $limit_info->duration = 'week';
            $limit_info->duration_int = (int) UserLimits::LIMIT_WEEKLY;
            $limit_info->limit_per = -1;
        }

        return $limit_info;
    }

    public static function findSpent(User $user, int $category_id, object $info): float
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($info->limit_per == -1) {
            $transactions = $user->getTransactions()->where('returned', false);
            $activity_transactions = $user->getActivityTransactions('returned', false);
        } else {
            $carbon_string = Carbon::now()->subDays($info->duration == 'day' ? 1 : 7)->toDateTimeString();

            $transactions = $user->getTransactions()->where('created_at', '>=', $carbon_string)->where('returned', false);
            $activity_transactions = $user->getActivityTransactions()->where('created_at', '>=', $carbon_string)->where('returned', false);
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

                $item_info = ProductHelper::deserializeProduct($transaction_product, false);

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
