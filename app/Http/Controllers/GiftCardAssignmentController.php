<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GiftCard;

class GiftCardAssignmentController extends Controller
{
    public function store(GiftCard $giftCard, User $user)
    {
        if ($giftCard->expired()) {
            return redirect()->route('settings_gift-cards_view', $giftCard)->with('error', 'Cannot assign an expired gift card.');
        }

        $giftCard->assignments()->create([
            'user_id' => $user->id,
            'assigner_id' => auth()->id(),
        ]);

        return redirect()->route('settings_gift-cards_view', $giftCard)->with('success', "Assigned gift card to {$user->full_name}.");
    }

    public function destroy(GiftCard $giftCard, User $user)
    {
        $assignment = $giftCard->assignments()->where('user_id', $user->id);
        $assignment->update(['deleted_by' => auth()->id()]);
        $assignment->delete();

        return redirect()->route('settings_gift-cards_view', $giftCard)->with('success', "Unassigned gift card from {$user->full_name}.");
    }

    public function ajaxUserSearch(GiftCard $giftCard): string
    {
        $users = User::query()
                        ->where('full_name', 'LIKE', '%' . request('search') . '%')
                        ->limit(7)
                        ->get()
                        ->all();
        $output = '';

        foreach ($users as $user) {
            $output .=
                '<tr>' .
                    '<td>' . $user->full_name . '</td>' .
                    ($user->giftCards->contains($giftCard)
                        ? '<td><button class="button is-success is-small" disabled>Add</button></td>'
                        : '<td><a href="' . route('settings_gift-cards_assign', [$giftCard->id, $user->id]) . '" class="button is-success is-small">Assign</a></td>') .
                '</tr>';
        }

        return $output;
    }
}
