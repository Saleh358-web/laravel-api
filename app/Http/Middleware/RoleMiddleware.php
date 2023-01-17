<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $roles, $permissions = null)
    {
        if(!$request->user()->hasRole($roles)) {
            abort(404);
        }

        if($permissions !== null && !$request->user()->allowedTo($permissions)) {
            abort(404);
        }

        return $next($request);
    }
}
