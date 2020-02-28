<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class LoginController extends Controller
{
    public function auth(Request $request)
    {
        if (Auth::attempt($request->except(['_token']))) {
            return redirect('/');
        } else {
            return redirect('/login')
            ->withInput($request->all())
            ->withErrors(Auth::attempt($request->except(['_token'])));
        }
        return redirect('/');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->back();
    }
}
