<?php

namespace App\Http\Controllers\User;

use App\Models\Family;
use App\Models\Payout;
use Cknow\Money\Money;
use App\Enums\PayoutStatus;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayoutRequest;

class PayoutController extends Controller
{
    public function create(Family $family, FamilyMember $familyMember)
    {
        $user = $familyMember->user;

        $owing = $user->findOwing();

        if ($owing->isZero()) {
            return redirect()->route('users_view', $user)->with('error', "{$user->full_name} does not owe anything.");
        }

        return view('pages.user.family.members.payouts.form', [
            'user' => $user,
            'family' => $family,
            'familyMember' => $familyMember,
            'owing' => $owing->getAmount() / 100,
        ]);
    }

    public function store(PayoutRequest $request, Family $family, FamilyMember $familyMember): Checkout
    {
        $amount = Money::parse($request->amount)->getAmount();
        $payout = $familyMember->user->payouts()->create([
            'creator_id' => auth()->id(),
            'amount' => $amount,
            'status' => PayoutStatus::Pending,
        ]);

        return $familyMember->user->checkoutCharge($amount, 'Payout', customerOptions: [
            'name' => $familyMember->user->full_name,
        ], sessionOptions: [
            'success_url' => route('family_member_payout_success', [$family, $familyMember]) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('family_member_payout_cancel', [$family, $familyMember]) . '?session_id={CHECKOUT_SESSION_ID}',
            'metadata' => ['payout_id' => $payout->id],
        ]);
    }

    public function stripeSuccessCallback(Request $request, Family $family, FamilyMember $familyMember)
    {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            return;
        }

        $payout = Payout::find($session->metadata['payout_id']);
        $payout->update([
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_intent_id' => $session->payment_intent,
            'status' => PayoutStatus::Paid,
        ]);

        return redirect()->route('families_member_view', [$family, $familyMember])->with('success', 'Payout successful.');
    }

    public function stripeCancelCallback(Request $request, Family $family, FamilyMember $familyMember)
    {
        $sessionId = $request->get('session_id');

        if ($sessionId === null) {
            return;
        }

        $session = Cashier::stripe()->checkout->sessions->retrieve($sessionId);

        if ($session->payment_status !== 'unpaid') {
            return;
        }

        $payout = Payout::find($session->metadata['payout_id']);
        $payout->update([
            'stripe_checkout_session_id' => $session->id,
            'status' => PayoutStatus::Cancelled,
        ]);

        return redirect()->route('families_member_view', [$family, $familyMember])->with('success', 'Payout cancelled.');
    }
}
