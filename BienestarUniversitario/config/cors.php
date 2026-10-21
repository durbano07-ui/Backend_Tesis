<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // OWASP ASVS V14.5.3: lista blanca estricta de orígenes. Los orígenes de desarrollo
    // (localhost y red local) solo se aceptan fuera de producción.
    'allowed_origins' => [
        // Dominios de producción, separados por coma (variable CORS_ALLOWED_ORIGINS)
        ...array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', ''))),
        ...(env('APP_ENV') === 'production' ? [] : [
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:5174',
            'http://127.0.0.1:5174',
            'http://localhost:5175',
            'http://127.0.0.1:5175',
            'http://192.168.0.93:5173',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ]),
    ],

    'allowed_origins_patterns' => env('APP_ENV') === 'production' ? [] : [
        '#^http://(localhost|127\.0\.0\.1)(:\d+)?$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // La API usa tokens Bearer, no cookies: no se permiten credenciales de navegador entre orígenes
    'supports_credentials' => false,

];
