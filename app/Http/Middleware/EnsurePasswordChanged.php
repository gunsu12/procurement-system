<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if user is authenticated and is_first_login is true
        if ($user && $user->is_first_login) {

            // Allow access to password change routes and logout
            if ($request->routeIs('auth.first-login', 'auth.first-login.update', 'logout')) {
                return $next($request);
            }

            // Redirect to change password page with a message
            return redirect()->route('auth.first-login')
                ->with('warning', 'Please change your password before proceeding.');
        }

        return $next($request);
    }
}
