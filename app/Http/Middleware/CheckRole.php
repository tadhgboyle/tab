<?php

namespace App\Http\Middleware;

use App\Roles;
use Closure;

class CheckRole
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
        if (!Roles::canViewPage($request->user()->role, $request->route()->getName())) {
            return redirect()->back()->with('error', "You do not have permission to access that page.");
        } else return $next($request);
    }
}
