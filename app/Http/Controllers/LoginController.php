<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function login()
    {
        return view('pages.login');
    }

    public function auth(Request $request): RedirectResponse
    {
        if (Auth::attempt([
            'username' => $request->username,
            'password' => $request->password,
        ], true)) {
            return redirect()->intended();
        }

        return redirect()->route('login')->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('login');
    }
}
