<?php

namespace App\Helpers;

use stdClass;
use App\Models\User;
use Cknow\Money\Money;
use App\Models\UserLimits;
use Illuminate\Support\Carbon;
use App\Models\TransactionProduct;
use App\Models\ActivityRegistration;

// TODO this class is disgusting, needs a refactor
class UserLimitsHelper
{
    public static function canSpend(User $user, Money $spending, int $category_id, ?object $info = null): bool
    {
        if ($info === null) {
            $info = self::getInfo($user, $category_id);
        }

        if ($info->limit->equals(Money::parse(-1_00))) {
            return true;
        }

        $spent = self::findSpent($user, $category_id, $info);

        return $spent->add($spending)->lessThanOrEqual($info->limit);
    }

    public static function getInfo(User $user, int $category_id): stdClass
    {
        // TODO why doesn't this return an instance of UserLimits...?
        $info = UserLimits::query()
            ->where([['user_id', $user->id], ['category_id', $category_id]])
            ->select(['duration', 'limit'])
            ->get();

        $limit_info = new stdClass();

        if ($info->count()) {
            $info = $info->first();
            $limit_info->duration = $info->duration === UserLimits::LIMIT_DAILY ? 'day' : 'week';
            $limit_info->duration_int = $info->duration;
            $limit_info->limit = $info->limit;
        } else {
            $limit_info->duration = 'week';
            $limit_info->duration_int = UserLimits::LIMIT_WEEKLY;
            $limit_info->limit = Money::parse(-1_00);
        }

        return $limit_info;
    }

    public static function findSpent(User $user, int $category_id, object $info): Money
    {
        // If they have unlimited money (no limit set) for this category,
        // get all their transactions, as they have no limit set we dont need to worry about
        // when the transaction was created_at.
        if ($info->limit->equals(Money::parse(-1_00))) {
            $transactions = $user->transactions
                ->where('returned', false);
            $activity_registrations = $user->activityRegistrations
                ->where('returned', false);
        } else {
            $carbon_string = Carbon::now()->subDays($info->duration === 'day' ? 1 : 7)->toDateTimeString();

            $transactions = $user->transactions
                ->where('created_at', '>=', $carbon_string)
                ->where('returned', false);

            $activity_registrations = $user->activityRegistrations
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

        $category_spent = $category_spent->add(...$activity_registrations->filter(function (ActivityRegistration $activityRegistration) use ($category_id) {
            return $activityRegistration->category_id === $category_id;
        })->map->total_price);

        return $category_spent;
    }
}
