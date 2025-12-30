<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO OIDC Configuration
    |--------------------------------------------------------------------------
    */
    'base_url' => env('SSO_BASE_URL', 'https://sso.yourdomain.com'),
    'client_id' => env('SSO_CLIENT_ID'),
    'client_secret' => env('SSO_CLIENT_SECRET'),
    'redirect_uri' => env('SSO_REDIRECT_URI'),
    'scopes' => explode(',', env('SSO_SCOPES', 'openid,profile,email')),

    /*
    |--------------------------------------------------------------------------
    | Endpoints (auto-configured from base_url)
    |--------------------------------------------------------------------------
    */
    'authorize_url' => env('SSO_BASE_URL') . '/api/oidc/authorize',
    'token_url' => env('SSO_BASE_URL') . '/api/oidc/token',
    'userinfo_url' => env('SSO_BASE_URL') . '/api/oidc/userinfo',
    'logout_url' => env('SSO_BASE_URL') . '/api/oidc/logout',
];
