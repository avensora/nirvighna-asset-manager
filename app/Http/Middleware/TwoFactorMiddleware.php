<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasTwoFactorEnabled() && $request->session()->get('2fa_pending')) {
            if (!$request->routeIs('2fa.*') && !$request->routeIs('logout')) {
                return redirect()->route('2fa.challenge');
            }
        }

        return $next($request);
    }
}
