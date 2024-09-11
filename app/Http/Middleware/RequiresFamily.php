<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequiresFamily
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string $permission
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->family) {
            return $next($request);
        }

        return response()->view('pages.403');
    }
}
