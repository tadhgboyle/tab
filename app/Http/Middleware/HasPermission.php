<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /** @phpstan-ignore-next-line  */
        if (hasPermission($request->route()->action['permission'])) {
            return $next($request);
        }

        return response()->view('pages.403');
    }
}
