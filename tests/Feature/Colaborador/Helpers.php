<?php

use App\Models\Area;
use App\Models\AreaGeneral;
use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Ocupacion;
use App\Models\Puesto;
use App\Models\PuestoGeneral;
use App\Models\Subindustria;
use App\Models\Ubicacion;
use App\Models\User;

/**
 * Crea una empresa mínima para tests del módulo Colaboradores.
 * Requiere haber ejecutado el seeder Inicial (industrias, subindustrias).
 *
 * @param  array<string, mixed>  $overrides
 */
function crearEmpresaMinima(array $overrides = []): Empresa
{
    $industria = Industria::query()->first();
    $subindustria = Subindustria::query()->first();
    if (! $industria || ! $subindustria) {
        throw new RuntimeException('Ejecuta el seeder Inicial antes de crear empresa.');
    }
    $fechaInicio = now()->format('Y-m-d');
    $fechaFin = now()->addYear()->format('Y-m-d');

    return Empresa::withoutEvents(function () use ($industria, $subindustria, $fechaInicio, $fechaFin, $overrides) {
        return Empresa::create(array_merge([
            'nombre' => 'Empresa Test Colaboradores',
            'nombre_contacto' => 'Contacto Test',
            'email_contacto' => 'contacto@test.com',
            'telefono_contacto' => '5512345678',
            'movil_contacto' => '5518765432',
            'industria_id' => $industria->id,
            'sub_industria_id' => $subindustria->id,
            'email_facturacion' => 'facturacion@test.com',
            'fecha_inicio_contrato' => $fechaInicio,
            'fecha_fin_contrato' => $fechaFin,
            'num_usuarios_reportes' => 10,
            'activo' => true,
            'fecha_activacion' => now(),
            'nombre_app' => 'App Test',
            'link_descarga_app' => null,
            'app_android_id' => null,
            'app_ios_id' => null,
            'app_huawei_id' => null,
            'color_primario' => null,
            'color_secundario' => null,
            'color_terciario' => null,
            'color_cuarto' => null,
            'logo_url' => null,
            'tipo_comision' => 'PERCENTAGE',
            'comision_bisemanal' => 2.00,
            'comision_mensual' => 3.00,
            'comision_quincenal' => 2.50,
            'comision_semanal' => 1.50,
            'tiene_pagos_catorcenales' => true,
            'fecha_proximo_pago_catorcenal' => now()->addDays(14)->format('Y-m-d'),
            'tiene_sub_empresas' => false,
            'comision_gateway' => 0.50,
            'transacciones_con_imss' => true,
            'validar_cuentas_automaticamente' => true,
            'tiene_analiticas_por_ubicacion' => true,
            'version_android' => null,
            'version_ios' => null,
            'tiene_limite_de_sesiones' => false,
            'tiene_firma_nubarium' => false,
            'enviar_boletin' => true,
            'permitir_encuesta_salida' => false,
            'configuracion_app_id' => null,
            'activar_finiquito' => false,
            'url_finiquito' => null,
            'domiciliación_via_api' => false,
            'ha_firmado_nuevo_contrato' => false,
            'vigencia_mensajes_urgentes' => null,
            'permitir_notificaciones_felicitaciones' => false,
            'segmento_notificaciones_felicitaciones' => null,
            'permitir_retenciones' => false,
            'dias_vencidos_retencion' => 30,
            'pertenece_pepeferia' => null,
            'tipo_registro' => null,
            'descargar_cursos' => false,
        ], $overrides));
    });
}

/**
 * Crea una ubicación con campos obligatorios para tests (cp requerido por migración 190906).
 */
function crearUbicacion(Empresa $empresa, array $overrides = []): Ubicacion
{
    return $empresa->ubicaciones()->create(array_merge([
        'nombre' => 'Oficina Test',
        'cp' => '01001',
    ], $overrides));
}

/**
 * Crea un área general para la empresa (requerido por Area que tiene area_general_id obligatorio).
 */
function crearAreaGeneral(Empresa $empresa, array $overrides = []): AreaGeneral
{
    return $empresa->areasGenerales()->create(array_merge([
        'nombre' => 'Área General Test',
    ], $overrides));
}

/**
 * Crea un área con area_general_id obligatorio. Crea AreaGeneral si no se pasa.
 */
function crearArea(Empresa $empresa, array $overrides = []): Area
{
    if (isset($overrides['area_general_id'])) {
        $areaGeneral = AreaGeneral::findOrFail($overrides['area_general_id']);
        unset($overrides['area_general_id']);
    } else {
        $areaGeneral = crearAreaGeneral($empresa);
    }

    return $empresa->areas()->create(array_merge([
        'nombre' => 'Área Test',
        'area_general_id' => $areaGeneral->id,
    ], $overrides));
}

/**
 * Crea un puesto general para la empresa.
 */
function crearPuestoGeneral(Empresa $empresa, array $overrides = []): PuestoGeneral
{
    return $empresa->puestosGenerales()->create(array_merge([
        'nombre' => 'Puesto General Test',
    ], $overrides));
}

/**
 * Obtiene o crea una ocupación para tests (puestos requieren ocupacion_id).
 */
function obtenerOcupacionParaTest(): Ocupacion
{
    $ocupacion = Ocupacion::query()->first();
    if ($ocupacion !== null) {
        return $ocupacion;
    }

    return Ocupacion::create(['descripcion' => 'Ocupación Test']);
}

/**
 * Crea un puesto con puesto_general_id, ocupacion_id y area_general_id obligatorios.
 */
function crearPuesto(Empresa $empresa, array $overrides = []): Puesto
{
    $puestoGeneral = isset($overrides['puesto_general_id'])
        ? PuestoGeneral::find($overrides['puesto_general_id'])
        : crearPuestoGeneral($empresa);
    $ocupacion = isset($overrides['ocupacion_id'])
        ? Ocupacion::find($overrides['ocupacion_id'])
        : obtenerOcupacionParaTest();
    $areaGeneral = isset($overrides['area_general_id'])
        ? AreaGeneral::find($overrides['area_general_id'])
        : crearAreaGeneral($empresa);
    unset($overrides['puesto_general_id'], $overrides['ocupacion_id'], $overrides['area_general_id']);

    return $empresa->puestos()->create(array_merge([
        'nombre' => 'Puesto Test',
        'puesto_general_id' => $puestoGeneral->id,
        'ocupacion_id' => $ocupacion->id,
        'area_general_id' => $areaGeneral->id,
    ], $overrides));
}

/**
 * User colaborador + ficha en `colaboradores` (1:1) vía ColaboradorService.
 */
function crearUserColaborador(Empresa $empresa, array $overrides = []): User
{
    $defaults = [
        'name' => 'Colab',
        'apellido_paterno' => 'Test',
        'apellido_materno' => 'Uno',
        'email' => fake()->unique()->safeEmail(),
        'fecha_nacimiento' => '1990-01-15',
        'fecha_ingreso' => '2024-01-01',
        'periodicidad_pago' => 'QUINCENAL',
    ];
    $data = array_merge($defaults, $overrides);
    unset($data['empresa_id']);

    return app(\App\Services\ColaboradorService::class)->crearColaborador($data, $empresa);
}
