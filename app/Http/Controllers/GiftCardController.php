<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GiftCard;
use App\Helpers\CategoryHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\GiftCardRequest;

class GiftCardController extends Controller
{
    public function show(GiftCard $giftCard)
    {
        return view('pages.settings.gift-cards.view', [
            'giftCard' => $giftCard,
        ]);
    }

    public function create()
    {
        return view('pages.settings.gift-cards.form', [
            'categories' => resolve(CategoryHelper::class)->getCategories(),
        ]);
    }

    public function store(GiftCardRequest $request): RedirectResponse
    {
        $giftCard = new GiftCard();
        $giftCard->code = $request->code;
        $giftCard->original_balance = $request->balance;
        $giftCard->remaining_balance = $request->balance;
        $giftCard->expires_at = $request->expires_at;
        $giftCard->issuer_id = auth()->id();
        $giftCard->save();

        return redirect()->route('settings')->with('success', "Created new gift card {$giftCard->code()}.");
    }

    // TODO: Implement update method
    public function edit(GiftCard $giftCard)
    {
        return view('pages.settings.gift-cards.form', [
            'giftCard' => $giftCard,
        ]);
    }

    public function ajaxCheckValidity(): JsonResponse
    {
        $giftCard = GiftCard::firstWhere('code', request()->query('code'));

        if (!$giftCard) {
            return response()->json([
                'valid' => false,
                'message' => 'Invalid gift card code',
            ]);
        }

        if ($giftCard->expired()) {
            return response()->json([
                'valid' => false,
                'message' => 'Gift card has expired',
            ]);
        }

        if (!$giftCard->canBeUsedBy(User::find(request()->query('purchaser_id')))) {
            return response()->json([
                'valid' => false,
                'message' => 'Gift card cannot be used by you',
            ]);
        }

        if ($giftCard->fullyUsed()) {
            return response()->json([
                'valid' => false,
                'message' => 'Gift card has no remaining balance',
            ]);
        }

        return response()->json([
            'valid' => true,
            'remaining_balance' => $giftCard->remaining_balance->getAmount() / 100,
        ]);
    }
}
