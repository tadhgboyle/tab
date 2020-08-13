<?php

namespace App\Http\Middleware;

use App\Roles;
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
        $permissions = $request->route()->action['permission'];
        if (!is_array($permissions)) {
            if (!Roles::hasPermission($request->user()->role, $permissions)) {
                return redirect()->back()->with('error', "You do not have permission to access that page.");
            }
        } else {
            foreach ($permissions as $permission) {
                if (!Roles::hasPermission($request->user()->role, $permission)) {
                    return redirect()->back()->with('error', "You do not have permission to access that page.");
                }
            }
        }
        return $next($request);
    }
}
