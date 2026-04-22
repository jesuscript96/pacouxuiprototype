<?php

use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Models\User;
use App\Services\EmpresaService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Datos base para crear una Empresa con todos los toggles activos y campos alternos llenos.
 * Solo cambia tipo_comision y los campos de comisión (porcentaje vs mixto).
 *
 * @return array<string, mixed>
 */
function datosEmpresaCompletos(string $tipoComision): array
{
    $fechaInicio = now()->format('Y-m-d');
    $fechaFin = now()->addYear()->format('Y-m-d');
    $base = [
        'nombre' => 'Empresa Test '.$tipoComision,
        'nombre_contacto' => 'Contacto Test',
        'email_contacto' => 'contacto@empresatest.com',
        'telefono_contacto' => '5512345678',
        'movil_contacto' => '5518765432',
        'email_facturacion' => 'facturacion@empresatest.com',
        'industria_id' => 2,
        'sub_industria_id' => 2,
        'fecha_inicio_contrato' => $fechaInicio,
        'fecha_fin_contrato' => $fechaFin,
        'num_usuarios_reportes' => 10,
        'app_android_id' => 'com.test.app',
        'app_ios_id' => '123456789',
        'tipo_comision' => $tipoComision,
        'tiene_sub_empresas' => true,
        'tiene_analiticas_por_ubicacion' => true,
        'permitir_notificaciones_felicitaciones' => true,
        'segmento_notificaciones_felicitaciones' => 'COMPANY',
        'permitir_retenciones' => true,
        'emails_retenciones' => [
            ['email_retencion' => 'retenciones@test.com'],
        ],
        'dias_vencidos_retencion' => 30,
        'dia_retencion_mensual' => now()->addDays(5)->format('Y-m-d H:i:s'),
        'dia_retencion_semanal' => '1',
        'dia_retencion_catorcenal' => '5',
        'dia_retencion_quincenal' => '3',
        'tiene_pagos_catorcenales' => true,
        'fecha_proximo_pago_catorcenal' => now()->addDays(10)->format('Y-m-d'),
        'tiene_quincena_personalizada' => true,
        'dia_inicio' => '1',
        'dia_fin' => '15',
        'activo' => true,
        'tiene_limite_de_sesiones' => true,
        'activar_finiquito' => true,
        'url_finiquito' => 'https://finiquito.test/url',
        'permitir_encuesta_salida' => true,
        'razones' => ['ABANDONO', 'RENUNCIA'],
        'tiene_firma_nubarium' => true,
        'aplicacion_compilada' => true,
        'nombre_app' => 'Test App',
        'link_descarga_app' => 'https://app.test/download',
        'transacciones_con_imss' => true,
        'frecuencia_notificaciones_estado_animo' => 7,
        'vigencia_mensajes_urgentes' => 30,
        'validar_cuentas_automaticamente' => true,
        'enviar_boletin' => true,
        'domiciliación_via_api' => true,
        'descargar_cursos' => true,
        'razones_sociales' => [
            [
                'nombre' => 'Razón Social Test S.A.',
                'rfc' => 'XXX010101XXX',
                'cp' => '01000',
                'calle' => 'Calle Principal',
                'numero_exterior' => '100',
                'numero_interior' => 'A',
                'colonia' => 'Centro',
                'alcaldia' => 'Cuauhtémoc',
                'estado' => 'Ciudad de México',
            ],
        ],
        'color_primario' => '#FF5733',
        'color_secundario' => '#33FF57',
        'color_terciario' => '#3357FF',
        'color_cuarto' => '#F0F033',
        'centro_costo_belvo_id' => 90,
        'centro_costo_emida_id' => 91,
        'centro_costo_stp_id' => 89,
        'productos' => [
            ['producto_id' => 6, 'desde' => '1'],
        ],
        'alias_transaccion_nomina' => 'NOMINA',
        'alias_transaccion_servicio' => 'SERVICIO',
        'alias_transaccion_recarga' => 'RECARGA',
        'notificaciones_incluidas' => [
            1 => true,
            2 => true,
            3 => true,
            4 => true,
            5 => true,
            6 => true,
        ],
    ];

    if ($tipoComision === 'PERCENTAGE') {
        $base['comision_semanal'] = '1.5';
        $base['comision_bisemanal'] = '2';
        $base['comision_quincenal'] = '2.5';
        $base['comision_mensual'] = '3';
        $base['comision_gateway'] = '0.5';
    }

    if ($tipoComision === 'MIXED') {
        $base['rango_comision'] = [
            [
                'rango_comision_precio_desde' => '0',
                'rango_comision_precio_hasta' => '1000',
                'rango_comision_monto_fijo' => '10',
                'rango_comision_porcentaje' => '2',
            ],
            [
                'rango_comision_precio_desde' => '1001',
                'rango_comision_precio_hasta' => '5000',
                'rango_comision_monto_fijo' => '25',
                'rango_comision_porcentaje' => '1.5',
            ],
        ];
    }

    return $base;
}

beforeEach(function () {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->seed(\Database\Seeders\ReconocimientosSeeder::class);
    $this->seed(\Database\Seeders\TemaVozColaboradoresSeeder::class);

    ConfiguracionApp::create([
        'nombre_app' => 'Test App Config',
        'android_app_id' => 'com.test.app',
        'ios_app_id' => '123456789',
        'one_signal_app_id' => 'onesignal-test-id',
        'one_signal_rest_api_key' => 'onesignal-rest-key',
    ]);

    $this->user = User::first();
    $this->actingAs($this->user);
});

it('crea una empresa con tipo de comisión Porcentaje, todos los toggles activos y campos alternos guardados correctamente', function () {
    $data = datosEmpresaCompletos('PERCENTAGE');
    $service = app(EmpresaService::class);

    $empresa = $service->create($data);

    expect($empresa)->toBeInstanceOf(Empresa::class)
        ->and($empresa->id)->not->toBeNull()
        ->and($empresa->nombre)->toBe('Empresa Test PERCENTAGE')
        ->and($empresa->tipo_comision)->toBe('PERCENTAGE')
        ->and($empresa->comision_semanal)->toBe(1.5)
        ->and($empresa->comision_bisemanal)->toBe(2.0)
        ->and($empresa->comision_quincenal)->toBe(2.5)
        ->and($empresa->comision_mensual)->toBe(3.0)
        ->and($empresa->comision_gateway)->toBe(0.5)
        ->and($empresa->tiene_sub_empresas)->toBeTrue()
        ->and($empresa->tiene_analiticas_por_ubicacion)->toBeTrue()
        ->and($empresa->permitir_notificaciones_felicitaciones)->toBeTrue()
        ->and($empresa->segmento_notificaciones_felicitaciones)->toBe('COMPANY')
        ->and($empresa->permitir_retenciones)->toBeTrue()
        ->and($empresa->tiene_pagos_catorcenales)->toBeTrue()
        ->and($empresa->fecha_proximo_pago_catorcenal)->not->toBeNull()
        ->and($empresa->tiene_limite_de_sesiones)->toBeTrue()
        ->and($empresa->activar_finiquito)->toBeTrue()
        ->and($empresa->url_finiquito)->toBe('https://finiquito.test/url')
        ->and($empresa->permitir_encuesta_salida)->toBeTrue()
        ->and($empresa->tiene_firma_nubarium)->toBeTrue()
        ->and($empresa->transacciones_con_imss)->toBeTrue()
        ->and($empresa->validar_cuentas_automaticamente)->toBeTrue()
        ->and($empresa->enviar_boletin)->toBeTrue()
        ->and($empresa->domiciliación_via_api)->toBeTrue()
        ->and($empresa->descargar_cursos)->toBeTrue();

    $empresa->refresh();

    expect($empresa->razonesSociales)->toHaveCount(1)
        ->and($empresa->razonesSociales->first()->nombre)->toBe('Razón Social Test S.A.')
        ->and($empresa->productos)->toHaveCount(1)
        ->and($empresa->centrosCostos)->toHaveCount(3)
        ->and($empresa->comisionesRangos)->toHaveCount(0)
        ->and($empresa->configuracionRetencionNominas)->not->toBeEmpty()
        ->and($empresa->aliasTipoTransacciones)->toHaveCount(3)
        ->and($empresa->notificacionesIncluidas)->not->toBeEmpty();

    \Illuminate\Support\Facades\DB::table('quincenas_personalizadas')
        ->where('empresa_id', $empresa->id)
        ->first();
    expect(\Illuminate\Support\Facades\DB::table('quincenas_personalizadas')->where('empresa_id', $empresa->id)->exists())->toBeTrue();

    expect(\Illuminate\Support\Facades\DB::table('razones_encuesta_salida')->where('empresa_id', $empresa->id)->count())->toBe(2);
});

it('crea una empresa con tipo de comisión Mixto, todos los toggles activos y campos alternos guardados correctamente', function () {
    $data = datosEmpresaCompletos('MIXED');
    $service = app(EmpresaService::class);

    $empresa = $service->create($data);

    expect($empresa)->toBeInstanceOf(Empresa::class)
        ->and($empresa->id)->not->toBeNull()
        ->and($empresa->nombre)->toBe('Empresa Test MIXED')
        ->and($empresa->tipo_comision)->toBe('MIXED')
        ->and($empresa->comision_semanal)->toBe(0.0)
        ->and($empresa->comision_mensual)->toBe(0.0)
        ->and($empresa->tiene_sub_empresas)->toBeTrue()
        ->and($empresa->permitir_retenciones)->toBeTrue();

    $empresa->refresh();

    $rangos = $empresa->comisionesRangos;
    expect($rangos)->toHaveCount(2)
        ->and((float) $rangos[0]->precio_desde)->toBe(0.0)
        ->and((float) $rangos[0]->precio_hasta)->toBe(1000.0)
        ->and((float) $rangos[0]->cantidad_fija)->toBe(10.0)
        ->and((float) $rangos[0]->porcentaje)->toBe(2.0)
        ->and((float) $rangos[1]->precio_desde)->toBe(1001.0)
        ->and((float) $rangos[1]->precio_hasta)->toBe(5000.0)
        ->and((float) $rangos[1]->cantidad_fija)->toBe(25.0)
        ->and((float) $rangos[1]->porcentaje)->toBe(1.5);

    expect($empresa->razonesSociales)->toHaveCount(1)
        ->and($empresa->configuracionRetencionNominas)->not->toBeEmpty()
        ->and($empresa->aliasTipoTransacciones)->toHaveCount(3);
});

/*
 * TABLAS AFECTADAS por EmpresaService::create() al crear una Empresa:
 *
 * 1. empresas
 * 2. frecuencia_notificaciones (si se envía frecuencia_notificaciones_estado_animo)
 * 3. configuracion_retencion_nominas (si permitir_retenciones y algún dia_retencion_*)
 * 4. comisiones_rangos (solo si tipo_comision = MIXED y hay rango_comision)
 * 5. quincenas_personalizadas (si tiene_quincena_personalizada, dia_inicio, dia_fin)
 * 6. empresas_notificaciones_incluidas (pivot)
 * 7. razones_sociales (Razonsocial)
 * 8. empresas_razones_sociales (pivot)
 * 9. empresas_centros_costos (pivot)
 * 10. empresas_productos (pivot)
 * 11. empresas_reconocimientos (pivot, reconocimientos no exclusivos)
 * 12. empresas_temas_voz_colaboradores (pivot)
 * 13. razones_encuesta_salida (si permitir_encuesta_salida y razones)
 * 14. alias_tipo_transacciones (por cada alias_transaccion_* no vacío)
 * 15. logs (por el evento created del modelo Empresa)
 *
 * Opcionales (según datos): configuracion_app (lectura), almacenamiento (documentos, logo, foto).
 */
