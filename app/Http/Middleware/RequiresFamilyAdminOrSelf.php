<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFamilyAdminOrSelf
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->isFamilyAdmin($request->route('family')) || auth()->user()->familyMember()->is($request->route('familyMember'))) {
            return $next($request);
        }

        return response()->view('pages.403');
    }
}
