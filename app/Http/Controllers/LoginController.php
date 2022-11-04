<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login()
    {
        return view('pages.login');
    }

    public function auth(Request $request): RedirectResponse
    {
        if (Auth::attempt($request->except(['_token']))) {
            return redirect()->route('index');
        }

        return redirect()->route('login')->with('error', 'Invalid credentials. Please try again.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        return redirect()->route('index');
    }
}
