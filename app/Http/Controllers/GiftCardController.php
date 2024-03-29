<?php

namespace App\Http\Controllers;

use App\Helpers\Permission;
use Cknow\Money\Money;
use App\Models\GiftCard;
use App\Helpers\CategoryHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\GiftCardRequest;

class GiftCardController extends Controller
{
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
        $giftCard->issuer_id = auth()->id();
        $giftCard->save();

        return redirect()->route('settings')->with('success', "Created new gift card {$giftCard->code()}.");
    }

    public function edit(GiftCard $giftCard)
    {
        return view('pages.settings.gift-cards.form', [
            'giftCard' => $giftCard,
        ]);
    }

    public function update(GiftCardRequest $request, GiftCard $giftCard): RedirectResponse
    {
        $giftCard->code = $request->code;
        if (Money::parse($request->balance)->greaterThan($giftCard->original_balance)) {
            $giftCard->original_balance = $request->balance;
        }
        $giftCard->remaining_balance = $request->balance;
        $giftCard->save();

        return redirect()->route('settings')->with('success', "Edited gift card {$giftCard->code()}.");
    }

    public function delete(GiftCard $giftCard): RedirectResponse
    {
        $giftCard->delete();

        return redirect()->route('settings')->with('success', "Deleted gift card {$giftCard->code()}.");
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

    public function ajaxGetUses(GiftCard $giftCard): string
    {
        $output = '';

        foreach ($giftCard->uses as $transaction) {
            $output .=
                '<tr>' .
                    '<td>' . $transaction->id . (hasPermission(Permission::ORDERS_VIEW) ? ' <a href="' . route('orders_view', $transaction) . '">(View)</a>' : '') . '</td>' .
                    '<td>' . $transaction->created_at->format('M jS Y h:ia') . '</td>' .
                    '<td>' . $transaction->gift_card_amount->format() . '</td>' .
                '</tr>';
        }

        return $output;
    }
}
