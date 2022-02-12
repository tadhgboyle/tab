<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\PayoutRequest;
use App\Services\Payouts\PayoutCreationService;

class PayoutController extends Controller
{
    public function form(Request $request, User $user)
    {
        return view('pages.users.payouts.form', [
            'user' => $user,
            'owing' => (float) number_format($user->findOwing(), 2),
        ]);
    }

    public function new(PayoutRequest $request, User $user)
    {
        return (new PayoutCreationService($request, $user))->redirect();
    }
}
