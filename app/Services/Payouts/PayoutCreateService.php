<?php

namespace App\Services\Payouts;

use App\Models\User;
use App\Models\Payout;
use App\Services\HttpService;
use App\Http\Requests\PayoutRequest;
use Cknow\Money\Money;
use Illuminate\Http\RedirectResponse;

class PayoutCreateService extends HttpService
{
    use PayoutService;

    public const RESULT_INVALID_AMOUNT = 'INVALID_AMOUNT';

    public const RESULT_NOTHING_OWED = 'NOTHING_OWED';

    public const RESULT_OVER_OWED = 'OVER_OWED';

    public const RESULT_SUCCESS = 'SUCCESS';

    public function __construct(PayoutRequest $request, User $user)
    {
        $amount = Money::parse($request->amount);

        if (!$amount->isPositive()) {
            $this->_message = 'Amount must be above $0.00.';
            $this->_result = self::RESULT_INVALID_AMOUNT;
            return;
        }

        if ($user->findOwing()->isZero()) {
            $this->_message = 'User does not owe anything.';
            $this->_result = self::RESULT_NOTHING_OWED;
            return;
        }

        if ($amount->greaterThan($user->findOwing())) {
            $this->_message = 'Amount exceeds what user owes.';
            $this->_result = self::RESULT_OVER_OWED;
            return;
        }

        $payout = new Payout();

        $payout->identifier = $request->identifier;
        $payout->user_id = $user->id;
        $payout->cashier_id = auth()->id();
        $payout->amount = $amount;

        $user->payouts()->save($payout);

        $this->_payout = $payout;
        $this->_message = "Successfully created payout of $payout->amount for $user->full_name.";
        $this->_result = self::RESULT_SUCCESS;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            self::RESULT_SUCCESS => redirect()->route('users_view', $this->getPayout()->user)->with('success', $this->getMessage()),
            default => redirect()->back()->with('error', $this->getMessage()),
        };
    }
}
