<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\PayoutRequest;
use App\Services\Payouts\PayoutCreationService;

class PayoutController extends Controller
{
    public function create(User $user)
    {
        // TODO: don't allow creation if they don't owe anything
        return view('pages.users.payouts.form', [
            'user' => $user,
            'owing' => (float) number_format($user->findOwing(), 2),
        ]);
    }

    public function store(PayoutRequest $request, User $user): RedirectResponse
    {
        return (new PayoutCreationService($request, $user))->redirect();
    }
}
