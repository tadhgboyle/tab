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
        // TODO: don't allow creation if they don't owe anything
        return view('pages.users.payouts.form', [
            'user' => $user,
            'owing' => $user->findOwing()->getAmount() / 100,
        ]);
    }

    public function store(PayoutRequest $request, User $user): RedirectResponse
    {
        return (new PayoutCreateService($request, $user))->redirect();
    }
}
