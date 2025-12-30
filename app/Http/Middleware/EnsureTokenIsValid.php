<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\Auth\SSOController;
use Illuminate\Support\Facades\Auth;

class EnsureTokenIsValid
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
        // Check if token is about to expire (within 5 minutes)
        $expiresAt = session('sso_expires_at');

        if (Auth::check() && $expiresAt && now()->addMinutes(5)->isAfter($expiresAt)) {
            $controller = new SSOController();
            $refreshed = $controller->refreshToken();

            if (!$refreshed) {
                // Token refresh failed, redirect to login
                Auth::logout();
                return redirect()->route('sso.login');
            }
        }

        return $next($request);
    }
}
