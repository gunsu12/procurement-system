<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'oidc' => [
        'client_id' => env('SSO_CLIENT_ID'),
        'client_secret' => env('SSO_CLIENT_SECRET'),
        'redirect' => env('SSO_REDIRECT_URI'),
        'auth_base_uri' => env('SSO_BASE_URL') . '/api/oidc',
        'authorize_uri' => '/authorize',
        'token_uri' => '/token',
        'userinfo_uri' => '/userinfo',
        'scopes' => explode(',', env('SSO_SCOPES', 'openid,profile,email')),
    ],

    'hrs' => [
        'endpoint' => env('HRS_SYNC_ENDPOINT'),
        'token' => env('HRS_API_TOKEN'),
    ],
];
