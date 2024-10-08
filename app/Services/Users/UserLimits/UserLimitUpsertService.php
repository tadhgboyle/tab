<?php

namespace App\Services\Users\UserLimits;

use App\Models\User;
use Cknow\Money\Money;
use App\Models\Category;
use App\Models\UserLimit;
use App\Services\Service;
use Illuminate\Http\Request;
use App\Services\Users\UserService;

class UserLimitUpsertService extends Service
{
    use UserService;

    public const RESULT_SUCCESS = 'SUCCESS';
    public const RESULT_SUCCESS_NULL_DATA = 'SUCCESS_NULL_DATA';
    public const RESULT_NEGATIVE_LIMIT = 'RESULT_NEGATIVE_LIMIT';

    public function __construct(User $user, Request $data)
    {
        $limits_data = $data['limits'];
        $durations_data = $data['durations'];

        if (empty($limits_data)) {
            $this->_result = self::RESULT_SUCCESS_NULL_DATA;
            $this->_message = 'Limit data is empty';
            return;
        }

        foreach ($limits_data as $category_id => $limit) {
            // Default to $-1.00 if limit not typed in
            if (empty($limit) && $limit !== '0') {
                $limit = -1_00;
            }

            $limit = Money::parse($limit);

            if ($limit->lessThan(Money::parse(-1_00))) {
                // TODO update frontend to have a checkbox to indicate no limit rather than typing in $-1.00
                $this->_message = 'Limit must be $-1.00 or above for ' . Category::find($category_id)->name . '. ($-1.00 means no limit)';
                $this->_result = self::RESULT_NEGATIVE_LIMIT;
                return;
            }

            UserLimit::updateOrCreate([
                'user_id' => $user->id,
                'category_id' => $category_id,
            ], [
                'limit' => $limit,
                'duration' => $durations_data[$category_id],
            ]);
        }

        $this->_result = self::RESULT_SUCCESS;
    }
}
