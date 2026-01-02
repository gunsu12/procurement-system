<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    /**
     * Redirect to SSO login page
     */
    public function redirectToSSO(Request $request)
    {
        $state = Str::random(40);
        $nonce = Str::random(40);

        // Store state and nonce in session for verification
        session([
            'sso_state' => $state,
            'sso_nonce' => $nonce,
            'sso_intended' => $request->get('redirect', '/home'),
        ]);

        $query = http_build_query([
            'client_id' => config('sso.client_id'),
            'redirect_uri' => config('sso.redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(' ', config('sso.scopes')),
            'state' => $state,
            'nonce' => $nonce,
        ]);

        return redirect(config('sso.authorize_url') . '?' . $query);
    }

    /**
     * Handle SSO callback
     */
    public function handleCallback(Request $request)
    {
        // Verify state to prevent CSRF
        if ($request->state !== session('sso_state')) {
            return redirect('/login')->withErrors(['error' => 'Invalid state parameter']);
        }

        // Check for errors
        if ($request->has('error')) {
            return redirect('/login')->withErrors([
                'error' => $request->error_description ?? $request->error
            ]);
        }

        // Exchange authorization code for tokens
        try {
            $tokenResponse = Http::asForm()->post(config('sso.token_url'), [
                'grant_type' => 'authorization_code',
                'client_id' => config('sso.client_id'),
                'client_secret' => config('sso.client_secret'),
                'redirect_uri' => config('sso.redirect_uri'),
                'code' => $request->code,
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to exchange authorization code: ' . $tokenResponse->body());
            }

            $tokens = $tokenResponse->json();

            // Get user info from SSO
            $userResponse = Http::withToken($tokens['access_token'])
                ->get(config('sso.userinfo_url'));

            if (!$userResponse->successful()) {
                throw new \Exception('Failed to fetch user info: ' . $userResponse->body());
            }

            $ssoUser = (object) $userResponse->json();
            \Log::info('SSO User Info:', (array) $ssoUser);

            // Find or create user in local database
            $user = User::findOrCreateFromSSO($ssoUser);
            \Log::info('Local User ID: ' . $user->id);

            // Store tokens in session for later use (refresh, logout)
            session([
                'sso_access_token' => $tokens['access_token'],
                'sso_refresh_token' => $tokens['refresh_token'] ?? null,
                'sso_id_token' => $tokens['id_token'] ?? null,
                'sso_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            ]);

            // Login the user
            Auth::login($user, true);

            // Check if this is first login from SSO
            if ($user->is_first_login) {
                return redirect()->route('sso.password.reset')
                    ->with('info', 'Please set your password for security purposes.');
            }

            // Redirect to intended page
            $intended = session('sso_intended', '/home');
            session()->forget(['sso_state', 'sso_nonce', 'sso_intended']);

            return redirect($intended)->with('success', 'Successfully logged in!');

        } catch (\Exception $e) {
            \Log::error('SSO Callback Error: ' . $e->getMessage());
            return redirect('/login')->withErrors([
                'error' => 'Authentication failed. Please try again.'
            ]);
        }
    }

    /**
     * Logout from SSO and local session
     */
    public function logout(Request $request)
    {
        $idToken = session('sso_id_token');

        // Logout from local session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to SSO logout if we have id_token
        if ($idToken) {
            $query = http_build_query([
                'id_token_hint' => $idToken,
                'post_logout_redirect_uri' => url('/'),
            ]);

            return redirect(config('sso.logout_url') . '?' . $query);
        }

        return redirect('/');
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshToken()
    {
        $refreshToken = session('sso_refresh_token');

        if (!$refreshToken) {
            return false;
        }

        try {
            $tokenResponse = Http::asForm()->post(config('sso.token_url'), [
                'grant_type' => 'refresh_token',
                'client_id' => config('sso.client_id'),
                'client_secret' => config('sso.client_secret'),
                'refresh_token' => $refreshToken,
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Failed to refresh token: ' . $tokenResponse->body());
            }

            $tokens = $tokenResponse->json();

            // Update session with new tokens
            session([
                'sso_access_token' => $tokens['access_token'],
                'sso_refresh_token' => $tokens['refresh_token'] ?? $refreshToken,
                'sso_expires_at' => now()->addSeconds($tokens['expires_in'] ?? 3600),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Token Refresh Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Show password reset form for first-time SSO users
     */
    public function showPasswordResetForm()
    {
        $user = Auth::user();

        // Ensure user is logged in and needs to reset password
        if (!$user || !$user->is_first_login) {
            return redirect('/home');
        }

        return view('auth.sso-password-reset');
    }

    /**
     * Update password for first-time SSO users
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        // Ensure user is logged in and needs to reset password
        if (!$user || !$user->is_first_login) {
            return redirect('/home');
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Update user password and clear first login flag
        $user->update([
            'password' => bcrypt($request->password),
            'is_first_login' => false,
            'password_reset_at' => now(),
        ]);

        return redirect('/home')->with('success', 'Password set successfully! You can now use your credentials to login.');
    }
}
