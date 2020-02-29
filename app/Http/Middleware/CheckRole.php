<?php

namespace App\Http\Middleware;

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
    public function handle($request, Closure $next)
    {
        if ($request->user()->role != "administrator") {
            return redirect('/')->with('error', 'You do not have permission to view this page.');
        } else {
            return $next($request);
        }
    }
}
