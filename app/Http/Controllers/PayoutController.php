<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\PayoutRequest;
use Illuminate\Http\RedirectResponse;
use App\Services\Payouts\PayoutCreateService;

class PayoutController extends Controller
{
    public function create(User $user)
    {
        $owing = $user->findOwing();

        if ($owing->isZero()) {
            return redirect()->route('users_view', $user)->with('error', 'User does not owe anything.');
        }

        return view('pages.users.payouts.form', [
            'user' => $user,
            'owing' => $owing->getAmount() / 100,
        ]);
    }

    public function store(PayoutRequest $request, User $user): RedirectResponse
    {
        return (new PayoutCreateService($request, $user))->redirect();
    }
}
