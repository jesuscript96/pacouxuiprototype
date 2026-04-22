<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración por defecto (fallback)
    |--------------------------------------------------------------------------
    |
    | En multi-tenant, las credenciales reales vienen de configuracion_app por empresa.
    | Estos valores sirven para el cliente singleton del paquete y entornos sin tenant.
    |
    */
    'app_id' => env('ONESIGNAL_APP_ID'),
    'rest_api_url' => env('ONESIGNAL_REST_API_URL', 'https://api.onesignal.com'),
    'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
    'user_auth_key' => env('ONESIGNAL_USER_AUTH_KEY', env('USER_AUTH_KEY')),
    'guzzle_client_timeout' => (int) env('ONESIGNAL_GUZZLE_CLIENT_TIMEOUT', 0),
];
