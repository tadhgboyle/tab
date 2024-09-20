<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFamilyAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->isFamilyAdmin()) {
            return $next($request);
        }

        return abort(403);
    }
}
