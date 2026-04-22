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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'workos' => [
        'client_id' => env('WORKOS_CLIENT_ID'),
        'api_key' => env('WORKOS_API_KEY'),
        'redirect_uri' => env('WORKOS_REDIRECT_URL') ?: rtrim(env('APP_URL', 'http://localhost'), '/').'/auth/workos/callback',
        // Si es true, al cerrar sesión solo se redirige al login de la app (no se pasa por la página de logout de WorkOS).
        // Útil cuando WorkOS muestra error por bloqueadores (p. ej. Segment) o si no quieres redirigir al usuario fuera de la app.
        'skip_logout_redirect' => env('WORKOS_SKIP_LOGOUT_REDIRECT', true),
    ],

    'pumble' => [
        'webhook_url' => env('PUMBLE_WEBHOOK_URL'),
        'enabled' => filter_var(env('PUMBLE_NOTIFICATIONS_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    ],

    'nubarium' => [
        'user' => env('NUBARIUM_USER'),
        'password' => env('NUBARIUM_PASSWORD'),
        'ocr_url' => env('OCR_NUBARIUM'),
        'ine_url' => env('INE_NUBARIUM'),
        'csf_url' => env('CSF_NUBARIUM'),
        'biometrics_url' => env('BIOMETRICS_NUBARIUM'),
        'imss_url' => env('NUBARIUM_IMSS_URL', 'https://api.nubarium.com/imss/wh/v1'),
        'nom151_url' => env('NUBARIUM_NOM151_URL', 'https://firma.nubarium.com/nom151/v1/obtener-nom151'),
    ],

    'openai' => [
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1/'),
        'key' => env('OPENAI_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 60),
    ],

    'curp' => [
        'api_url' => env('API_VALIDA_CURP'),
        'token' => env('API_VALIDA_CURP_TOKEN'),
    ],

    'palenca' => [
        'url' => env('PALENCA_URL'),
        'key' => env('PALENCA_KEY_PRIVATE'),
        'key_public' => env('PALENCA_KEY_PUBLIC'),
        'webhook_user' => env('PALENCA_WEBHOOK_USER'),
        'webhook_password' => env('PALENCA_WEBHOOK_PASSWORD'),
    ],

];
