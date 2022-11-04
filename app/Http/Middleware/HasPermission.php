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
     * @param string $permission
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (hasPermission($permission)) {
            return $next($request);
        }

        return response()->view('pages.403');
    }
}
