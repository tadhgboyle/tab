<?php

namespace App\Helpers;

use Cknow\Money\Money;
use stdClass;
use App\Models\User;
use App\Models\Category;
use App\Models\UserLimits;
use Illuminate\Support\Carbon;
use App\Http\Requests\UserRequest;
use App\Models\TransactionProduct;
use App\Services\Users\UserEditService;
use App\Services\Users\UserCreationService;

// TODO: Move these to user model. $user->canSpendInCategory($cat_id, 5.99)
class UserLimitsHelper
{
    public static function createOrEditFromRequest(UserRequest $request, User $user, string $class): array
    {
        if ($request->limit === null) {
            return [null, null];
        }

        foreach ($request->limit as $category_id => $limit) {

            // Default to limit per day rather than week if not specified
            $duration = $request->duration[$category_id] ?? UserLimits::LIMIT_DAILY;

            // Default to $-1.00 if limit not typed in
            if (empty($limit) && $limit !== '0') {
                $limit = -1_00;
            }

            $limit = Money::parse($limit);

            if ($limit->lessThan(Money::parse(-1_00))) {
                $message = 'Limit must be $-1.00 or above for ' . Category::find($category_id)->name . '. ($-1.00 means no limit)';
                $result = ($class === UserCreationService::class)
                            ? UserCreationService::RESULT_INVALID_LIMIT
                            : UserEditService::RESULT_INVALID_LIMIT;
                return [$message, $result];
            }

            UserLimits::updateOrCreate([
                'user_id' => $user->id,
                'category_id' => $category_id,
            ], [
                'limit_per' => $limit,
                'duration' => $duration,
            ]);
        }

        return [null, null];
    }

    public static function canSpend(User $user, Money $spending, int $category_id, ?object $info = null): bool
    {
        if ($info === null) {
            $info = self::getInfo($user, $category_id);
        }

        if ($info->limit_per->equals(Money::parse(-1_00))) {
            return true;
        }

        $spent = self::findSpent($user, $category_id, $info);

        return $spent->add($spending)->lessThanOrEqual($info->limit_per);
    }

    public static function getInfo(User $user, int $category_id): stdClass
    {
        $info = UserLimits::query()
            ->where([['user_id', $user->id], ['category_id', $category_id]])
            ->select(['duration', 'limit_per'])
            ->get();

        $limit_info = new stdClass();

        if ($info->count()) {
            $info = $info->first();
            $limit_info->duration = $info->duration === UserLimits::LIMIT_DAILY ? 'day' : 'week';
            $limit_info->duration_int = $info->duration;
            $limit_info->limit_per = $info->limit_per;
        } else {
            $limit_info->duration = 'week';
            $limit_info->duration_int = UserLimits::LIMIT_WEEKLY;
            $limit_info->limit_per = Money::parse(-1_00);
        }

        return $limit_info;
    }

    public static function findSpent(User $user, int $category_id, object $info): Money
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($info->limit_per->equals(Money::parse(-1_00))) {
            $transactions = $user->transactions->where('returned', false);
            $activity_transactions = $user->getActivityTransactions();
        } else {
            $carbon_string = Carbon::now()->subDays($info->duration === 'day' ? 1 : 7)->toDateTimeString();

            $transactions = $user->transactions
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);

            $activity_transactions = $user->getActivityTransactions()
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);
        }

        $category_spent = Money::parse(0);

        foreach ($transactions as $transaction) {

            // Loop transaction products. Determine if the product's category is the one we are looking at,
            // if so, add its ((value * (quantity - returned)) * tax) to the end result
            foreach ($transaction->products->filter(fn (TransactionProduct $product) => $product->category_id === $category_id) as $product) {
                $quantity_available = $product->quantity - $product->returned;

                $category_spent = $category_spent->add(TaxHelper::calculateFor($product->price, $quantity_available, $product->pst !== null, [
                    'gst' => $product->gst,
                    'pst' => $product->pst,
                ]));
            }
        }

        foreach ($activity_transactions as $activity_transaction) {
            if ($activity_transaction->category_id !== $category_id) {
                continue;
            }

            $category_spent = $category_spent->add(Money::parse($activity_transaction->total_price));
        }

        return $category_spent;
    }
}
