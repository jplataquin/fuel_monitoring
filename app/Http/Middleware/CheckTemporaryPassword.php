<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTemporaryPassword
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_temporary_password && ! $request->routeIs('profile.edit_password', 'profile.update_password', 'logout')) {
            return redirect()->route('profile.edit_password');
        }

        return $next($request);
    }
}
