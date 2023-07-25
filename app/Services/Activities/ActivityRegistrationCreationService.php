<?php

namespace App\Services\Activities;

use App\Models\User;
use App\Models\Activity;
use App\Services\Service;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Helpers\UserLimitsHelper;
use App\Models\ActivityRegistration;
use Illuminate\Http\RedirectResponse;

class ActivityRegistrationCreationService extends Service
{
    use ActivityService;
    use ActivityRegistrationService;

    public const RESULT_ALREADY_REGISTERED = 0;
    public const RESULT_OUT_OF_SLOTS = 1;
    public const RESULT_NO_BALANCE = 2;
    public const RESULT_OVER_USER_LIMIT = 3;
    public const RESULT_SUCCESS = 4;

    public function __construct(Activity $activity, User $user)
    {
        if ($activity->isAttending($user)) {
            $this->_result = self::RESULT_ALREADY_REGISTERED;
            $this->_message = "Could not register {$user->full_name} for {$activity->name}, they are already attending this activity.";
            return;
        }

        if (!$activity->hasSlotsAvailable()) {
            $this->_result = self::RESULT_OUT_OF_SLOTS;
            $this->_message = "Could not register {$user->full_name} for {$activity->name}, this activity is out of slots.";
            return;
        }

        if ($activity->getPriceAfterTax() > $user->balance) {
            $this->_result = self::RESULT_NO_BALANCE;
            $this->_message = "Could not register {$user->full_name} for {$activity->name}, they do not have enough balance.";
            return;
        }

        if (!UserLimitsHelper::canSpend($user, $activity->getPriceAfterTax(), $activity->category->id)) {
            $this->_result = self::RESULT_OVER_USER_LIMIT;
            $this->_message = "Could not register {$user->full_name} for {$activity->name}, they have reached their limit for the {$activity->category->name} category.";
            return;
        }

        $registration = new ActivityRegistration();
        $registration->user_id = $user->id;
        $registration->cashier_id = auth()->id();
        $registration->activity_id = $activity->id;
        $registration->activity_price = $activity->price;
        $registration->category_id = $activity->category_id;
        $registration->activity_gst = resolve(SettingsHelper::class)->getGst();
        $registration->activity_pst = $activity->pst ? resolve(SettingsHelper::class)->getPst() : null;
        $registration->total_price = $activity->getPriceAfterTax();
        $registration->rotation_id = resolve(RotationHelper::class)->getCurrentRotation()->id;
        $registration->save();

        $user->balance = $user->balance->subtract($activity->getPriceAfterTax());

        $this->_activity = $activity;
        $this->_activity_registration = $registration;
        $this->_result = self::RESULT_SUCCESS;
        $this->_message = "Successfully registered $user->full_name for $activity->name.";
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->back()->with([
                'success' => $this->getMessage(),
            ]),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
