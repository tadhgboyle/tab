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
        return view('payouts.form', [
            'user' => $user,
        ]);
    }

    public function new(PayoutRequest $request, User $user)
    {
        return (new PayoutCreationService($request, $user))->redirect();
    }
}
