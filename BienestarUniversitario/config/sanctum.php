<?php

use Laravel\Sanctum\Sanctum;

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,localhost:5173')),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate incoming requests. If none of these
    | guards return a valid user, the SPA will be used to authenticate.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal access tokens do
    | not expire. This won't tweak the lifetime of tokens that have already
    | been issued.
    |
    */

    'expiration' => (int) env('SANCTUM_EXPIRATION_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | additional security measures offered by some of the authentication
    | providers at your disposal. Please review the documentation before
    | changing this value.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum CSRF Header
    |--------------------------------------------------------------------------
    |
    | This value sets the headers for Sanctum to use when validating CSRF
    | tokens on incoming API requests.
    |
    */

    'csrf_header' => 'X-CSRF-TOKEN',

    /*
    |--------------------------------------------------------------------------
    | Ability Middleware
    |--------------------------------------------------------------------------
    |
    | When this value is true, Sanctum's built-in ability middleware will
    | be executed on any route that handles an incoming request that
    | matches one of the defined abilities.
    |
    */

    'middleware_middleware_ability_checks_on_invalid_tokens' => true,

];