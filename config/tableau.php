<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | URL base del sitio Tableau (sin barra final)
    |--------------------------------------------------------------------------
    |
    | Ejemplo: https://us-east-1.online.tableau.com
    |
    */
    'base_url' => rtrim((string) env('TABLEAU_URL', ''), '/'),

    /*
    |--------------------------------------------------------------------------
    | Connected App (JWT para incrustar vistas)
    |--------------------------------------------------------------------------
    */
    'connected_app' => [
        'client_id' => env('TABLEAU_CLIENT_ID'),
        'secret_id' => env('TABLEAU_SECRET_ID'),
        'secret_key' => env('TABLEAU_SECRET_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Usuario Tableau para incrustación “global” (legacy admin@paco.app)
    |--------------------------------------------------------------------------
    |
    | Panel Admin (super_admin o tipo administrador): claim «sub» del JWT de incrustación.
    | En panel Cliente se usa el correo de la empresa o usuario_tableau del usuario.
    |
    */
    'embed_admin_username' => env('TABLEAU_EMBED_ADMIN_USERNAME', 'admin@paco.app'),

    /*
    |--------------------------------------------------------------------------
    | Vigencia del token JWT (minutos)
    |--------------------------------------------------------------------------
    */
    'token_ttl_minutes' => (int) env('TABLEAU_TOKEN_TTL_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Script de incrustación Tableau Embedding API v3
    |--------------------------------------------------------------------------
    */
    'embedding_script_url' => env(
        'TABLEAU_EMBEDDING_SCRIPT_URL',
        'https://us-east-1.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js'
    ),

    /*
    |--------------------------------------------------------------------------
    | Empresa legacy: vista alternativa de rotación (Palacio de Hierro)
    |--------------------------------------------------------------------------
    |
    | ID de empresa en pacov3; la ruta alternativa se define en report_path_overrides.
    |
    */
    'palacio_hierro_empresa_id' => (int) env('TABLEAU_PALACIO_HIERRO_EMPRESA_ID', 54),

    /*
    |--------------------------------------------------------------------------
    | Rutas de vista alternativas por informe y empresa (fragmento tras base_url)
    |--------------------------------------------------------------------------
    |
    | Clave = slug del informe (config/tableau_reports.php). Subclave = id de empresa.
    |
    */
    'report_path_overrides' => [
        'rotacion_personal' => [
            (int) env('TABLEAU_PALACIO_HIERRO_EMPRESA_ID', 54) => 'RotacionPH/RotacindePersonal',
        ],
    ],

];
