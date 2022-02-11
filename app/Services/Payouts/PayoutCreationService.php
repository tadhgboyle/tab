<?php

namespace App\Services\Payouts;

use App\Models\User;
use App\Models\Payout;
use App\Services\Service;
use App\Http\Requests\PayoutRequest;
use Illuminate\Http\RedirectResponse;

class PayoutCreationService extends Service
{
    use PayoutService;

    public const RESULT_SUCCESS = 0;

    public function __construct(PayoutRequest $request, User $user)
    {
        $payout = new Payout();

        $payout->identifier = $request->identifier;
        $payout->user_id = $user->id;
        $payout->cashier_id = auth()->id();
        $payout->amount = $request->amount;

        $user->payouts()->save($payout);

        $this->_payout = $payout;
        $this->_message = "Successfully created payout of \${$payout->amount} for {$user->full_name}.";
        $this->_result = self::RESULT_SUCCESS;
    }

    public function redirect(): RedirectResponse
    {
        return match ($this->getResult()) {
            default => redirect()->route('users_view', $this->getPayout()->user)->with('success', $this->getMessage()),
        };
    }
}
