<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFamilyAdminOrSelf
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->isFamilyAdmin() || auth()->user()->id === $request->route('familyMember')->user_id) {
            return $next($request);
        }

        return abort(403);
    }
}
