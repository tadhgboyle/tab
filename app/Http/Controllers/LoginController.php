<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function auth(Request $request)
    {
        if (Auth::attempt($request->except(['_token']))) {
            return redirect()->route('index');
        }
        
        return redirect()->route('login')->withInput($request->all())->with('error', 'Invalid credentials. Please try again.');
    }

    public function login()
    {
        return view('pages.login');
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('index');
    }
}
