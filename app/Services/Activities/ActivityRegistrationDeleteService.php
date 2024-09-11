<?php

namespace App\Services\Activities;

use App\Services\HttpService;
use App\Models\ActivityRegistration;
use Illuminate\Http\RedirectResponse;

class ActivityRegistrationDeleteService extends HttpService
{
    use ActivityService;
    use ActivityRegistrationService;

    public const RESULT_ALREADY_RETURNED = 'ALREADY_RETURNED';
    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(ActivityRegistration $activityRegistration)
    {
        if ($activityRegistration->returned) {
            $this->_result = self::RESULT_ALREADY_RETURNED;
            $this->_message = "{$activityRegistration->user->full_name} has already been removed from this activity.";
            return;
        }

        $user = $activityRegistration->user;
        $refundAmount = $activityRegistration->total_price;

        $activityRegistration->update(['returned' => true]);
        $user->update(['balance' => $user->balance->add($refundAmount),]);

        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "{$user->full_name} has been removed from the activity and refunded {$refundAmount}.";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
