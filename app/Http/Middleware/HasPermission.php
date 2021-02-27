<?php

namespace App\Http\Middleware;

use Closure;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(\Illuminate\Http\Request $request, Closure $next)
    {
        if (hasPermission($request->route()->action['permission'])) {
            return $next($request);
        }

        return view('pages.403')->with('error', 'You do not have permission to access that page.');
    }
}
