<?php

namespace App\Http\Middleware;

use App\User;
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
        if (!User::canViewPage($request->user()->group, $request->route()->getName())) {
            return redirect('/')->with('error', 'You do not have permission to view this page.');
        } else return $next($request);
    }
}
