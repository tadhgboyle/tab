<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresOwnFamily
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->family?->is($request->route('family'))) {
            return $next($request);
        }

        return abort(403);
    }
}
