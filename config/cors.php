<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Habilitado para las rutas de la API y para la ruta de la cookie CSRF
    | de Sanctum (necesaria si el frontend SPA usa autenticacion con cookies).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', '*')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // true es necesario si el SPA usa axios con withCredentials para Sanctum cookie auth
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', true),

];
