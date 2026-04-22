<?php

/**
 * Script de verificación de traducción tablas/campos (inglés → español).
 * Ejecutar desde Tinker: require database_path('scripts/verificar_traducciones.php');
 * O vía comando: php artisan verificar:traducciones
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$outputFile = $GLOBALS['verificar_traducciones_output_file'] ?? storage_path('app/traducciones_pendientes_'.date('Ymd_His').'.json');

$traducciones = [
    'campos' => [
        'name' => 'nombre',
        'description' => 'descripcion',
        'type' => 'tipo',
        'status' => 'estado',
        'title' => 'titulo',
        'content' => 'contenido',
        'body' => 'contenido',
        'start_date' => 'fecha_inicio',
        'end_date' => 'fecha_fin',
        'created_by' => 'creado_por',
        'updated_by' => 'actualizado_por',
        'active' => 'activo',
        'is_active' => 'activo',
        'is_deleted' => 'eliminado',
        'deleted' => 'eliminado',
        'parent_id' => 'padre_id',
        'order' => 'orden',
        'position' => 'orden',
        'file_url' => 'url_archivo',
        'image_url' => 'url_imagen',
        'video_url' => 'url_video',
        'phone' => 'telefono',
        'mobile' => 'celular',
        'address' => 'direccion',
        'email' => 'correo',
        'message' => 'mensaje',
        'read_at' => 'leido_en',
        'amount' => 'monto',
        'concept' => 'concepto',
    ],
    'tablas_patrones' => [
        'histories' => 'historiales',
        '_history' => '_historial',
        'messages' => 'mensajes',
        'rooms' => 'salas',
        'employees' => 'empleados',
        'companies' => 'empresas',
        'products' => 'productos',
        'categories' => 'categorias',
        'questions' => 'preguntas',
        'responses' => 'respuestas',
        'surveys' => 'encuestas',
        'requests' => 'solicitudes',
        'notifications' => 'notificaciones',
        'transactions' => 'transacciones',
        'accounts' => 'cuentas',
        'payments' => 'pagos',
        'documents' => 'documentos',
        'contracts' => 'contratos',
        'folders' => 'carpetas',
        'files' => 'archivos',
        'advances' => 'adelantos',
        'receipts' => 'recibos',
        'templates' => 'plantillas',
        'devices' => 'dispositivos',
        'locations' => 'ubicaciones',
        'candidates' => 'candidatos',
        'recruitment' => 'reclutamiento',
        'reactions' => 'reacciones',
        'mentions' => 'menciones',
    ],
];

$excepciones_campos = [
    'id',
    'created_at',
    'updated_at',
    'deleted_at',
    'token',
    'api_key',
    'secret',
    'hash',
    'uuid',
    'workos_id',
    'password',
    'remember_token',
    'email_verified_at',
    'two_factor',
    'config',
    'json',
];

$excepciones_campos_contienen = [
    'belvo_',
    'nomipay_',
    'spatie_',
    'oauth_',
    '_token',
    '_at',
    '_id', // FKs: empleado_id, usuario_id, etc.
];

$excepciones_tablas = [
    'jobs',
    'job_batches',
    'failed_jobs',
    'cache',
    'cache_locks',
    'password_reset_tokens',
    'password_resets',
    'sessions',
    'oauth_',
    'spatie_',
    'personal_access_tokens',
    'model_has_',
    'role_has_',
];

$tablasObj = DB::select('SHOW TABLES');
$resultado = [
    'fecha' => now()->toDateTimeString(),
    'total_tablas' => count($tablasObj),
    'tablas_ingles' => [],
    'campos_ingles' => [],
    'resumen' => [],
];

foreach ($tablasObj as $tablaObj) {
    $tabla = current((array) $tablaObj);

    $tablaExcepcion = false;
    foreach ($excepciones_tablas as $exc) {
        if (str_starts_with($tabla, $exc) || str_contains($tabla, $exc)) {
            $tablaExcepcion = true;
            break;
        }
    }
    if ($tablaExcepcion) {
        continue;
    }

    $tablaEnIngles = false;
    foreach ($traducciones['tablas_patrones'] as $patron => $espanol) {
        $contienePatron = str_contains($tabla, $patron);
        $yaEnEspanol = str_contains($tabla, $espanol);
        if ($contienePatron && ! $yaEnEspanol) {
            $tablaEnIngles = true;
            break;
        }
    }
    if ($tablaEnIngles) {
        $resultado['tablas_ingles'][] = $tabla;
    }

    try {
        $columnas = Schema::getColumnListing($tabla);
        foreach ($columnas as $columna) {
            $esExcepcion = in_array($columna, $excepciones_campos, true);
            if (! $esExcepcion) {
                foreach ($excepciones_campos_contienen as $exc) {
                    if (str_contains($columna, $exc)) {
                        $esExcepcion = true;
                        break;
                    }
                }
            }
            if ($esExcepcion) {
                continue;
            }

            foreach ($traducciones['campos'] as $ingles => $espanol) {
                $coincide = $columna === $ingles || str_ends_with($columna, '_'.$ingles);
                if ($coincide) {
                    $sugerencia = $columna === $ingles
                        ? $espanol
                        : substr($columna, 0, -strlen($ingles) - 1).'_'.$espanol;
                    $resultado['campos_ingles'][] = [
                        'tabla' => $tabla,
                        'campo' => $columna,
                        'sugerencia' => $sugerencia,
                    ];
                    break;
                }
            }
        }
    } catch (Throwable) {
        continue;
    }
}

foreach ($resultado['campos_ingles'] as $campo) {
    $t = $campo['tabla'];
    if (! isset($resultado['resumen'][$t])) {
        $resultado['resumen'][$t] = [];
    }
    $resultado['resumen'][$t][] = [
        'campo' => $campo['campo'],
        'sugerencia' => $campo['sugerencia'],
    ];
}

file_put_contents(
    $outputFile,
    json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

if (function_exists('echo')) {
    echo '✅ Reporte generado: '.$outputFile."\n";
    echo '📊 Tablas en inglés detectadas: '.count($resultado['tablas_ingles'])."\n";
    echo '📝 Campos en inglés detectados: '.count($resultado['campos_ingles'])."\n";
}

return [
    'output_file' => $outputFile,
    'resultado' => $resultado,
];
