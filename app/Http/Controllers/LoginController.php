<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    public function auth(Request $request)
    {
        if (Auth::attempt($request->except(['_token']))) {
            return redirect()->route('index');
        } else {
            return redirect()->route('login')->withInput($request->all())->with('error', 'Invalid credentials. Please try again.');
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('index');
    }
}
