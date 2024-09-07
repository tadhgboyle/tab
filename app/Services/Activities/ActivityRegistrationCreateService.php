<?php

namespace App\Services\Activities;

use App\Models\User;
use App\Models\Activity;
use App\Services\HttpService;
use App\Helpers\RotationHelper;
use App\Helpers\SettingsHelper;
use App\Models\ActivityRegistration;
use Illuminate\Http\RedirectResponse;

class ActivityRegistrationCreateService extends HttpService
{
    use ActivityService;
    use ActivityRegistrationService;

    public const RESULT_ALREADY_REGISTERED = 'ALREADY_REGISTERED';
    public const RESULT_OUT_OF_SLOTS = 'OUT_OF_SLOTS';
    public const RESULT_NO_BALANCE = 'NO_BALANCE';
    public const RESULT_OVER_USER_LIMIT = 'OVER_USER_LIMIT';
    public const RESULT_SUCCESS = 'SUCCESS';

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

        if (!$user->limitFor($activity->category)->canSpend($activity->getPriceAfterTax())) {
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
            self::RESULT_SUCCESS => redirect()->back()->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
