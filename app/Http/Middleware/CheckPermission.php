<?php

namespace App\Http\Middleware;

use Closure;

class CheckPermission
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
        if (!$request->user()->hasPermission($request->route()->action['permission'])) {
            return redirect()->route('index')->with('error', "You do not have permission to access that page.");
        }

        return $next($request);
    }
}
