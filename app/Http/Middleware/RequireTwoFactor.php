<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTwoFactor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated, let other middleware handle it
        if (!$user) {
            return $next($request);
        }

        // If user has 2FA enabled but hasn't verified it in this session
        if ($user->hasEnabledTwoFactor() && !$request->session()->get('2fa_verified')) {
            // Don't redirect if already on 2FA pages to avoid loops
            if ($request->routeIs('tenant.two-factor.*')) {
                return $next($request);
            }

            return redirect()->route('tenant.two-factor.challenge');
        }

        return $next($request);
    }
}